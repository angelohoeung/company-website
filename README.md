# Company Website

A company website made to be used as a administrative dashboard. Built using PHP, Tailwind CSS, and daisyUI. Definitely NOT secure.

## Dependencies

- PHP + mysqli
- MySQL
- Node.js

## Local Development

1. Start the MySQL Server instance
2. Run the queries found in `database/create.sql`
3. Run the queries found in `database/insert.sql`
4. Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

5. Enter your credentials into `.env`
6. Install dependencies:

```bash
npm install
```

7. Compile CSS:

```bash
npx tailwindcss -i ./input.css -o ./output.css --watch
```

8. Run the PHP server:

```bash
php -S localhost:8000
```

9. Visit [http://localhost:8000](http://localhost:8000)

## Deploying

1. Create the database using a tool like phpMyAdmin
2. Run the queries found in `database/create.sql` and `database/insert.sql`
3. Enter credentials into `.env`
4. Minify CSS:

```bash
npx tailwindcss -i ./input.css -o ./output.css --minify
```

5. Deploy
