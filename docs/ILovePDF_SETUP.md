# iLovePDF Setup

Follow these steps to configure the project to use the iLovePDF API for DOCX→PDF conversion.

1. Install the SDK (already required in composer.json during setup):

   ```bash
   composer require ilovepdf/ilovepdf-php
   ```

2. Add API keys to your `.env`:

   ```env
   ILOVEPDF_PUBLIC_KEY=your_public_key
   ILOVEPDF_SECRET_KEY=your_secret_key
   ```

   Get keys from https://developer.ilovepdf.com/ (create an account and generate keys).

3. Clear config cache:

   ```bash
   php artisan config:clear
   ```

4. Test conversion by approving a `LetterRequest` in the application; the service will use iLovePDF exclusively and will throw an error if the SDK or keys are missing.

Notes:
- The code has been changed to use iLovePDF only. If you prefer local conversion, revert changes in `app/Services/LetterDocumentService.php`.
- If Composer scripts fail due to `.env` parsing, ensure `.env` is valid JSON/KEY=VALUE format (no stray characters).
