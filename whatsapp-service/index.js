const express = require('express');
const path = require('path');
const fs = require('fs/promises');
const mime = require('mime-types');
const axios = require('axios');
const pino = require('pino');
const qrcode = require('qrcode-terminal');
const {
  default: makeWASocket,
  useMultiFileAuthState,
  fetchLatestBaileysVersion,
  DisconnectReason,
  jidNormalizedUser,
} = require('@whiskeysockets/baileys');
const { Boom } = require('@hapi/boom');

const app = express();
const logger = pino({ level: process.env.LOG_LEVEL || 'info' });
const port = Number(process.env.PORT || 3001);
const authDir = path.join(__dirname, 'auth_info_baileys');

app.use(express.json({ limit: '10mb' }));

let sock = null;
let isReady = false;

function normalizePhone(phone) {
  if (!phone || typeof phone !== 'string') {
    return null;
  }

  let normalized = phone.replace(/[^0-9]/g, '');

  if (normalized.startsWith('0')) {
    normalized = `62${normalized.slice(1)}`;
  }

  if (!normalized.startsWith('62')) {
    normalized = `62${normalized}`;
  }

  return normalized;
}

function toJid(phone) {
  const normalized = normalizePhone(phone);
  if (!normalized) {
    return null;
  }

  return jidNormalizedUser(`${normalized}@s.whatsapp.net`);
}

function getFileNameFromUrl(fileUrl) {
  try {
    const url = new URL(fileUrl);
    const base = path.basename(url.pathname);
    return base || `document-${Date.now()}.pdf`;
  } catch {
    return `document-${Date.now()}.pdf`;
  }
}

async function loadFileFromPath(filePath) {
  const absolutePath = path.isAbsolute(filePath)
    ? filePath
    : path.join(process.cwd(), filePath);

  const buffer = await fs.readFile(absolutePath);
  const fileName = path.basename(absolutePath);
  const mimeType = mime.lookup(fileName) || 'application/pdf';

  return {
    buffer,
    fileName,
    mimeType,
  };
}

async function loadFileFromUrl(fileUrl) {
  const response = await axios.get(fileUrl, {
    responseType: 'arraybuffer',
    timeout: 30000,
  });

  const contentType = response.headers['content-type'];
  const fileName = getFileNameFromUrl(fileUrl);

  return {
    buffer: Buffer.from(response.data),
    fileName,
    mimeType: contentType || mime.lookup(fileName) || 'application/pdf',
  };
}

async function startSocket() {
  const { state, saveCreds } = await useMultiFileAuthState(authDir);
  const { version } = await fetchLatestBaileysVersion();

  sock = makeWASocket({
    auth: state,
    version,
    printQRInTerminal: false,
    logger: pino({ level: 'silent' }),
    syncFullHistory: false,
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', async (update) => {
    const { connection, qr, lastDisconnect } = update;

    if (qr) {
      logger.info('QR code tersedia. Silakan scan di WhatsApp.');
      qrcode.generate(qr, { small: true });
    }

    if (connection === 'open') {
      isReady = true;
      logger.info('WhatsApp terhubung.');
    }

    if (connection === 'close') {
      isReady = false;
      const boom = lastDisconnect?.error instanceof Boom
        ? lastDisconnect.error
        : new Boom(lastDisconnect?.error || 'Connection closed');
      const statusCode = boom.output.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

      logger.warn({ statusCode }, 'Koneksi WhatsApp tertutup.');

      if (shouldReconnect) {
        logger.info('Mencoba reconnect WhatsApp...');
        await startSocket();
      } else {
        logger.error('Session logout. Hapus auth_info_baileys lalu login ulang dengan scan QR.');
      }
    }
  });
}

app.get('/health', (_, res) => {
  res.json({
    ok: true,
    connected: isReady,
  });
});

app.post('/send-message', async (req, res) => {
  try {
    const { phone, message } = req.body;

    if (!phone || !message) {
      return res.status(422).json({
        ok: false,
        message: 'Parameter phone dan message wajib diisi.',
      });
    }

    if (!sock || !isReady) {
      return res.status(503).json({
        ok: false,
        message: 'WhatsApp belum terhubung.',
      });
    }

    const jid = toJid(phone);

    if (!jid) {
      return res.status(422).json({
        ok: false,
        message: 'Nomor telepon tidak valid.',
      });
    }

    await sock.sendMessage(jid, { text: message });

    return res.json({ ok: true });
  } catch (error) {
    logger.error({ err: error }, 'Gagal kirim pesan teks');
    return res.status(500).json({
      ok: false,
      message: error.message || 'Terjadi kesalahan saat mengirim pesan.',
    });
  }
});

app.post('/send-document', async (req, res) => {
  try {
    const { phone, message, fileUrl, filePath } = req.body;

    if (!phone || !message) {
      return res.status(422).json({
        ok: false,
        message: 'Parameter phone dan message wajib diisi.',
      });
    }

    if (!fileUrl && !filePath) {
      return res.status(422).json({
        ok: false,
        message: 'Salah satu dari fileUrl atau filePath wajib diisi.',
      });
    }

    if (!sock || !isReady) {
      return res.status(503).json({
        ok: false,
        message: 'WhatsApp belum terhubung.',
      });
    }

    const jid = toJid(phone);

    if (!jid) {
      return res.status(422).json({
        ok: false,
        message: 'Nomor telepon tidak valid.',
      });
    }

    const filePayload = fileUrl
      ? await loadFileFromUrl(fileUrl)
      : await loadFileFromPath(filePath);

    await sock.sendMessage(jid, {
      document: filePayload.buffer,
      mimetype: filePayload.mimeType,
      fileName: filePayload.fileName,
      caption: message,
    });

    return res.json({ ok: true });
  } catch (error) {
    logger.error({ err: error }, 'Gagal kirim dokumen');
    return res.status(500).json({
      ok: false,
      message: error.message || 'Terjadi kesalahan saat mengirim dokumen.',
    });
  }
});

app.listen(port, async () => {
  logger.info(`WhatsApp service berjalan di port ${port}`);
  await startSocket();
});
