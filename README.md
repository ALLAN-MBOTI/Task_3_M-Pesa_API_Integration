# Task_3_M-Pesa_API_Integration

To Develop a REST API to handle C2B M-Pesa transaction callbacks and persist the data into a database.

---

## Instructions & Requirements

The API should:

* **Receive JSON Payloads:** Accept incoming C2B transaction callbacks from Safaricom M-Pesa (Daraja API).
* **Parse & Extract Fields:** Parse all incoming parameters (`TransactionType`, `TransID`, `TransTime`, `TransAmount`, `BusinessShortCode`, `BillRefNumber`, `InvoiceNumber`, `OrgAccountBalance`, `ThirdPartyTransID`, `MSISDN`, `FirstName`, `MiddleName`, `LastName`) and explicitly convert them into string fields.
* **Data Persistence:** Store all parsed transaction details securely into the database without duplication.
* **Structured JSON Response:** Return a standardized response (`ResultCode: 0`, `ResultDesc: "Confirmation received successfully"`) with HTTP status `200 OK` to confirm receipt to Safaricom.

---

## Deployment Manual

### 1. System Requirements

* **PHP:** `^8.2`
* **Composer:** `^2.x`
* **Database:** MySQL `^8.0` / PostgreSQL `^14` / SQLite
* **Laravel Framework:** `^11.x`
* **Tunneling Tool (for local development):** Ngrok or Cloudflare Tunnel

---

### 2. Installation & Setup Steps

1. **Clone the Repository & Navigate to Project Directory:**
```bash
git clone https://github.com/ALLAN-MBOTI/Task_3_M-Pesa_API_Integration.git
cd Task_3_M-Pesa_API_Integration/src

```


2. **Install PHP Dependencies:**
```bash
composer install

```


3. **Configure Environment File:**
Copy the `.env.example` file to `.env`:
```bash
cp .env.example .env

```


Configure your database credentials inside `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mpesa_c2b_db
DB_USERNAME=root
DB_PASSWORD=secret

```


4. **Generate Application Key:**
```bash
php artisan key:generate

```


5. **Run Database Migrations:**
Execute the migration to build the `mpesa_c2b_transactions` table:
```bash
php artisan migrate

```


6. **Enable API Routes & Clear Route Cache:**
Ensure API routing is enabled and routes are re-indexed:
```bash
php artisan install:api
php artisan route:clear

```


7. **Start the Laravel Server:**
```bash
php artisan serve

```


The local application will run on `[http://127.0.0.1:8000](http://127.0.0.1:8000)`.

---

### 3. Exposing Endpoint to Safaricom Daraja (Local Environment)

Safaricom C2B callbacks require a publicly accessible HTTPS URL.

1. Start an Ngrok tunnel pointing to your local Laravel server:
```bash
ngrok http 8000

```


2. Copy the generated HTTPS forwarding URL (e.g., `[https://a1b2c3d4.ngrok-free.app](https://a1b2c3d4.ngrok-free.app)`).
3. Your M-Pesa C2B Confirmation Callback URL will be:
`[https://a1b2c3d4.ngrok-free.app/api/mpesa/c2b/confirmation](https://a1b2c3d4.ngrok-free.app/api/mpesa/c2b/confirmation)`

---

## Test Manual

### 1. Automated Unit & Feature Testing

The project includes an automated test suite (`MpesaC2bTest`) covering valid payload ingestion, response structure, database persistence, and duplicate callback idempotency.

**Execute the test suite via Artisan:**

```bash
php artisan test --filter=MpesaC2bTest

```

**Expected Output:**

```text
  PASS  Tests\Feature\MpesaC2bTest
  ✓ mpesa c2b callback saves data and returns success response
  ✓ mpesa c2b callback handles duplicate transactions

  Tests:    2 passed (5 assertions)
  Duration: 0.32s

```

---

### 2. Manual Testing via `curl`

To simulate a Safaricom Daraja C2B callback, run the following `curl` command in your terminal:

```bash
curl -X POST http://127.0.0.1:8000/api/mpesa/c2b/confirmation \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "TransactionType": "Pay Bill",
    "TransID": "RKT9876543",
    "TransTime": "20260810120000",
    "TransAmount": "2500.00",
    "BusinessShortCode": "600000",
    "BillRefNumber": "INV-2026-001",
    "InvoiceNumber": "",
    "OrgAccountBalance": "150000.00",
    "ThirdPartyTransID": "",
    "MSISDN": "254712345678",
    "FirstName": "Allan",
    "MiddleName": "K",
    "LastName": "Mboti"
  }'

```

**Expected JSON Response (HTTP 200 OK):**

```json
{
  "ResultCode": 0,
  "ResultDesc": "Confirmation received successfully"
}

```

---

### 3. Verification Checklist

1. **HTTP Status Code:** Confirm the response returns `HTTP/1.1 200 OK`.
2. **Database Record:** Query the database to ensure all JSON keys were converted and saved into string attributes:
```sql
SELECT * FROM mpesa_c2b_transactions WHERE trans_id = 'RKT9876543';

```


3. **Idempotency Check:** Re-run the `curl` command with the same `TransID`. Verify that the endpoint returns `200 OK` and updates the record rather than throwing a duplicate key constraint exception.