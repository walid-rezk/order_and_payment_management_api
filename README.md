# Order and Payment Management API

RESTful APIs built with Laravel for managing orders and payments securely. Using the Strategy Design Pattern to make the payment infrastructure infinitely extendable, allowing to add a new payment gateways with minimal effort.

---

## 🚀 Setup Instructions (Dockerized)

This project is fully dockerized using a custom Docker Compose orchestration. Follow these steps to spin up the local development environment.


### Step-by-Step Installation

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/walid-rezk/order_and_payment_management_api.git
   cd order_and_payment_management_api
2. **Environment Variables:**
   ```bash
   cp .env.example .env
3. **Build the Application:**
   ```bash
   docker compose up -d --build
4. **Install Composer Dependencies inside the Container:**
   ```bash
   docker compose exec app composer install
5. **Generate Application and JWT Security Keys:**
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan jwt:secret
6. **Run Database Migrations and Seeders:**
   ```bash
   docker compose exec app php artisan migrate --seed
7. **Clear Application Cache:**
   ```bash
   docker compose exec app php artisan config:clear
   docker compose exec app php artisan cache:clear
***Now, the APIs is fully accessible at: http://localhost:8080***

8. **Running the Test:**
   ```bash
   docker compose exec app php artisan test
9. **Import postman collection JSON file:**
   
   On the root directory, there's a file `Order and Payment Management API.postman_collection.json` just import it on postman.

## 🛠️ Payment Gateway Extensibility (Strategy Pattern)
The system implements the Strategy Design Pattern managed through a central Service Container Provider, allowing new payment methods to be integrated without altering core controllers or existing checkout flows.

**How to Add a New Payment Gateway (EX: Stripe)**
1. **Create the Gateway Strategy Class:**
   Create `App\Services\PaymentGateways\StripeGateway.php` and implement the interface `App\Contracts\PaymentGatewayInterface`:

   ```php
   <?php

   namespace App\Services\PaymentGateways;

   use App\Contracts\PaymentGatewayInterface;
   use App\DTOs\PaymentResult;
   use App\Models\Order;
   use Illuminate\Support\Str;

   class StripeGateway implements PaymentGatewayInterface
   {
      private string $apiKey;
      private string $secret;

      public function __construct()
      {
         $this->apiKey = config('gateways.stripe.api_key', '');
         $this->secret = config('gateways.stripe.secret', '');
      }

      /**
      * {@inheritdoc}
      */
      public function getName(): string
      {
         return 'stripe';
      }

      /**
      * Simulate stripe payment processing.
      */
      public function processPayment(Order $order, float $amount): PaymentResult
      {
         // Validate gateway configuration
         if (empty($this->apiKey) || empty($this->secret)) {
               return PaymentResult::failed('Stripe gateway is not configured.');
         }

         // Simulate payment processing, this would be an API call on production
         $transactionId = 'stripe_txn_' . Str::uuid()->toString();

         return PaymentResult::successful(
               transactionId: $transactionId,
               message: "Stripe payment of \${$amount} processed successfully.",
         );
      }

      /**
      * {@inheritdoc}
      */
      public function supportsMethod(string $method): bool
      {
         return $method === 'stripe';
      }
   }
2. **Bootstrap the new gateway inside the Service Provider:**
   Open `App\Providers\PaymentServiceProvider.php` and register your new class inside the `boot()` method:

   ```php
   public function boot(): void
   {
      /** @var PaymentManager $manager */
      $manager = $this->app->make(PaymentManager::class);

      $manager->registerGateway(new CreditCardGateway());
      $manager->registerGateway(new PaypalGateway());
      $manager->registerGateway(new StripeGateway()); // the new one should be added
   }
2. **Add the new configuration to `config/gateways.php`:**
   ```php
   <?php

   return [

      /*
      |--------------------------------------------------------------------------
      | Payment Gateway Configuration
      |--------------------------------------------------------------------------
      |
      | Configure credentials for each payment gateway. These values are
      | read from your .env file. To add a new gateway, add a new entry
      | here and create the corresponding gateway class.
      |
      */

      'credit_card' => [
         'api_key' => env('CREDIT_CARD_API_KEY', ''),
         'secret'  => env('CREDIT_CARD_SECRET', ''),
      ],

      'paypal' => [
         'client_id' => env('PAYPAL_CLIENT_ID', ''),
         'secret'    => env('PAYPAL_SECRET', ''),
      ],

      // the new gateway
      'stripe' => [
         'api_key' => env('STRIPE_API_KEY', ''),
         'secret'  => env('STRIPE_SECRET', ''),
      ],

   ];
3. **Add the new environment variables to `.env` file:**
   ```.env
   STRIPE_API_KEY=stripe_test_key_11111
   STRIPE_SECRET=stripe_test_secret_6666
## 📝 Additional Notes
1. **Payment Restriction**: Payments can only be processed if an order's status is explicitly marked as `confirmed`. Attempting to pay for a `pending` or `cancelled` order throws a `422 Unprocessable Entity` validation exception.

2. **Deletion Cascade Guard**: Orders cannot be deleted if they have any payments associated with them `(422 Unprocessable Entity)`. Orders without payments can be deleted safely along with their associated order items.

3. **Data Ownership**: All operations inside OrderController are scoped strictly to `auth()->id()`. A logged-in user cannot view, update, or delete orders belonging to another user id  returns `(404 Not Found)`.