<?php

// File generated from our OpenAPI spec

// Stripe singleton
require __DIR__ . '/Stripe/Stripe.php';

// Utilities
require __DIR__ . '/Stripe/Util/CaseInsensitiveArray.php';
require __DIR__ . '/Stripe/Util/LoggerInterface.php';
require __DIR__ . '/Stripe/Util/DefaultLogger.php';
require __DIR__ . '/Stripe/Util/RandomGenerator.php';
require __DIR__ . '/Stripe/Util/RequestOptions.php';
require __DIR__ . '/Stripe/Util/Set.php';
require __DIR__ . '/Stripe/Util/Util.php';
require __DIR__ . '/Stripe/Util/ObjectTypes.php';

// HttpClient
require __DIR__ . '/Stripe/HttpClient/ClientInterface.php';
require __DIR__ . '/Stripe/HttpClient/StreamingClientInterface.php';
require __DIR__ . '/Stripe/HttpClient/CurlClient.php';

// Exceptions
require __DIR__ . '/Stripe/Exception/ExceptionInterface.php';
require __DIR__ . '/Stripe/Exception/ApiErrorException.php';
require __DIR__ . '/Stripe/Exception/ApiConnectionException.php';
require __DIR__ . '/Stripe/Exception/AuthenticationException.php';
require __DIR__ . '/Stripe/Exception/BadMethodCallException.php';
require __DIR__ . '/Stripe/Exception/CardException.php';
require __DIR__ . '/Stripe/Exception/IdempotencyException.php';
require __DIR__ . '/Stripe/Exception/InvalidArgumentException.php';
require __DIR__ . '/Stripe/Exception/InvalidRequestException.php';
require __DIR__ . '/Stripe/Exception/PermissionException.php';
require __DIR__ . '/Stripe/Exception/RateLimitException.php';
require __DIR__ . '/Stripe/Exception/SignatureVerificationException.php';
require __DIR__ . '/Stripe/Exception/UnexpectedValueException.php';
require __DIR__ . '/Stripe/Exception/UnknownApiErrorException.php';

// OAuth exceptions
require __DIR__ . '/Stripe/Exception/OAuth/ExceptionInterface.php';
require __DIR__ . '/Stripe/Exception/OAuth/OAuthErrorException.php';
require __DIR__ . '/Stripe/Exception/OAuth/InvalidClientException.php';
require __DIR__ . '/Stripe/Exception/OAuth/InvalidGrantException.php';
require __DIR__ . '/Stripe/Exception/OAuth/InvalidRequestException.php';
require __DIR__ . '/Stripe/Exception/OAuth/InvalidScopeException.php';
require __DIR__ . '/Stripe/Exception/OAuth/UnknownOAuthErrorException.php';
require __DIR__ . '/Stripe/Exception/OAuth/UnsupportedGrantTypeException.php';
require __DIR__ . '/Stripe/Exception/OAuth/UnsupportedResponseTypeException.php';

// API operations
require __DIR__ . '/Stripe/ApiOperations/All.php';
require __DIR__ . '/Stripe/ApiOperations/Create.php';
require __DIR__ . '/Stripe/ApiOperations/Delete.php';
require __DIR__ . '/Stripe/ApiOperations/NestedResource.php';
require __DIR__ . '/Stripe/ApiOperations/Request.php';
require __DIR__ . '/Stripe/ApiOperations/Retrieve.php';
require __DIR__ . '/Stripe/ApiOperations/Update.php';

// Plumbing
require __DIR__ . '/Stripe/ApiResponse.php';
require __DIR__ . '/Stripe/RequestTelemetry.php';
require __DIR__ . '/Stripe/StripeObject.php';
require __DIR__ . '/Stripe/ApiRequestor.php';
require __DIR__ . '/Stripe/ApiResource.php';
require __DIR__ . '/Stripe/SingletonApiResource.php';
require __DIR__ . '/Stripe/Service/AbstractService.php';
require __DIR__ . '/Stripe/Service/AbstractServiceFactory.php';

// StripeClient
require __DIR__ . '/Stripe/BaseStripeClientInterface.php';
require __DIR__ . '/Stripe/StripeClientInterface.php';
require __DIR__ . '/Stripe/StripeStreamingClientInterface.php';
require __DIR__ . '/Stripe/BaseStripeClient.php';
require __DIR__ . '/Stripe/StripeClient.php';

// Stripe API Resources
require __DIR__ . '/Stripe/Account.php';
require __DIR__ . '/Stripe/AccountLink.php';
require __DIR__ . '/Stripe/AlipayAccount.php';
require __DIR__ . '/Stripe/ApplePayDomain.php';
require __DIR__ . '/Stripe/ApplicationFee.php';
require __DIR__ . '/Stripe/ApplicationFeeRefund.php';
require __DIR__ . '/Stripe/Balance.php';
require __DIR__ . '/Stripe/BalanceTransaction.php';
require __DIR__ . '/Stripe/BankAccount.php';
require __DIR__ . '/Stripe/BillingPortal/Configuration.php';
require __DIR__ . '/Stripe/BillingPortal/Session.php';
require __DIR__ . '/Stripe/BitcoinReceiver.php';
require __DIR__ . '/Stripe/BitcoinTransaction.php';
require __DIR__ . '/Stripe/Capability.php';
require __DIR__ . '/Stripe/Card.php';
require __DIR__ . '/Stripe/Charge.php';
require __DIR__ . '/Stripe/Checkout/Session.php';
require __DIR__ . '/Stripe/Collection.php';
require __DIR__ . '/Stripe/CountrySpec.php';
require __DIR__ . '/Stripe/Coupon.php';
require __DIR__ . '/Stripe/CreditNote.php';
require __DIR__ . '/Stripe/CreditNoteLineItem.php';
require __DIR__ . '/Stripe/Customer.php';
require __DIR__ . '/Stripe/CustomerBalanceTransaction.php';
require __DIR__ . '/Stripe/Discount.php';
require __DIR__ . '/Stripe/Dispute.php';
require __DIR__ . '/Stripe/EphemeralKey.php';
require __DIR__ . '/Stripe/ErrorObject.php';
require __DIR__ . '/Stripe/Event.php';
require __DIR__ . '/Stripe/ExchangeRate.php';
require __DIR__ . '/Stripe/File.php';
require __DIR__ . '/Stripe/FileLink.php';
require __DIR__ . '/Stripe/Identity/VerificationReport.php';
require __DIR__ . '/Stripe/Identity/VerificationSession.php';
require __DIR__ . '/Stripe/Invoice.php';
require __DIR__ . '/Stripe/InvoiceItem.php';
require __DIR__ . '/Stripe/InvoiceLineItem.php';
require __DIR__ . '/Stripe/Issuing/Authorization.php';
require __DIR__ . '/Stripe/Issuing/Card.php';
require __DIR__ . '/Stripe/Issuing/CardDetails.php';
require __DIR__ . '/Stripe/Issuing/Cardholder.php';
require __DIR__ . '/Stripe/Issuing/Dispute.php';
require __DIR__ . '/Stripe/Issuing/Transaction.php';
require __DIR__ . '/Stripe/LineItem.php';
require __DIR__ . '/Stripe/LoginLink.php';
require __DIR__ . '/Stripe/Mandate.php';
require __DIR__ . '/Stripe/Order.php';
require __DIR__ . '/Stripe/OrderItem.php';
require __DIR__ . '/Stripe/OrderReturn.php';
require __DIR__ . '/Stripe/PaymentIntent.php';
require __DIR__ . '/Stripe/PaymentMethod.php';
require __DIR__ . '/Stripe/Payout.php';
require __DIR__ . '/Stripe/Person.php';
require __DIR__ . '/Stripe/Plan.php';
require __DIR__ . '/Stripe/Price.php';
require __DIR__ . '/Stripe/Product.php';
require __DIR__ . '/Stripe/PromotionCode.php';
require __DIR__ . '/Stripe/Quote.php';
require __DIR__ . '/Stripe/Radar/EarlyFraudWarning.php';
require __DIR__ . '/Stripe/Radar/ValueList.php';
require __DIR__ . '/Stripe/Radar/ValueListItem.php';
require __DIR__ . '/Stripe/Recipient.php';
require __DIR__ . '/Stripe/RecipientTransfer.php';
require __DIR__ . '/Stripe/Refund.php';
require __DIR__ . '/Stripe/Reporting/ReportRun.php';
require __DIR__ . '/Stripe/Reporting/ReportType.php';
require __DIR__ . '/Stripe/Review.php';
require __DIR__ . '/Stripe/SetupAttempt.php';
require __DIR__ . '/Stripe/SetupIntent.php';
require __DIR__ . '/Stripe/Sigma/ScheduledQueryRun.php';
require __DIR__ . '/Stripe/SKU.php';
require __DIR__ . '/Stripe/Source.php';
require __DIR__ . '/Stripe/SourceTransaction.php';
require __DIR__ . '/Stripe/Subscription.php';
require __DIR__ . '/Stripe/SubscriptionItem.php';
require __DIR__ . '/Stripe/SubscriptionSchedule.php';
require __DIR__ . '/Stripe/TaxCode.php';
require __DIR__ . '/Stripe/TaxId.php';
require __DIR__ . '/Stripe/TaxRate.php';
require __DIR__ . '/Stripe/Terminal/ConnectionToken.php';
require __DIR__ . '/Stripe/Terminal/Location.php';
require __DIR__ . '/Stripe/Terminal/Reader.php';
require __DIR__ . '/Stripe/ThreeDSecure.php';
require __DIR__ . '/Stripe/Token.php';
require __DIR__ . '/Stripe/Topup.php';
require __DIR__ . '/Stripe/Transfer.php';
require __DIR__ . '/Stripe/TransferReversal.php';
require __DIR__ . '/Stripe/UsageRecord.php';
require __DIR__ . '/Stripe/UsageRecordSummary.php';
require __DIR__ . '/Stripe/WebhookEndpoint.php';

// Services
require __DIR__ . '/Stripe/Service/AccountService.php';
require __DIR__ . '/Stripe/Service/AccountLinkService.php';
require __DIR__ . '/Stripe/Service/ApplePayDomainService.php';
require __DIR__ . '/Stripe/Service/ApplicationFeeService.php';
require __DIR__ . '/Stripe/Service/BalanceService.php';
require __DIR__ . '/Stripe/Service/BalanceTransactionService.php';
require __DIR__ . '/Stripe/Service/BillingPortal/ConfigurationService.php';
require __DIR__ . '/Stripe/Service/BillingPortal/SessionService.php';
require __DIR__ . '/Stripe/Service/ChargeService.php';
require __DIR__ . '/Stripe/Service/Checkout/SessionService.php';
require __DIR__ . '/Stripe/Service/CountrySpecService.php';
require __DIR__ . '/Stripe/Service/CouponService.php';
require __DIR__ . '/Stripe/Service/CreditNoteService.php';
require __DIR__ . '/Stripe/Service/CustomerService.php';
require __DIR__ . '/Stripe/Service/DisputeService.php';
require __DIR__ . '/Stripe/Service/EphemeralKeyService.php';
require __DIR__ . '/Stripe/Service/EventService.php';
require __DIR__ . '/Stripe/Service/ExchangeRateService.php';
require __DIR__ . '/Stripe/Service/FileService.php';
require __DIR__ . '/Stripe/Service/FileLinkService.php';
require __DIR__ . '/Stripe/Service/Identity/VerificationReportService.php';
require __DIR__ . '/Stripe/Service/Identity/VerificationSessionService.php';
require __DIR__ . '/Stripe/Service/InvoiceService.php';
require __DIR__ . '/Stripe/Service/InvoiceItemService.php';
require __DIR__ . '/Stripe/Service/Issuing/AuthorizationService.php';
require __DIR__ . '/Stripe/Service/Issuing/CardService.php';
require __DIR__ . '/Stripe/Service/Issuing/CardholderService.php';
require __DIR__ . '/Stripe/Service/Issuing/DisputeService.php';
require __DIR__ . '/Stripe/Service/Issuing/TransactionService.php';
require __DIR__ . '/Stripe/Service/MandateService.php';
require __DIR__ . '/Stripe/Service/OrderService.php';
require __DIR__ . '/Stripe/Service/OrderReturnService.php';
require __DIR__ . '/Stripe/Service/PaymentIntentService.php';
require __DIR__ . '/Stripe/Service/PaymentMethodService.php';
require __DIR__ . '/Stripe/Service/PayoutService.php';
require __DIR__ . '/Stripe/Service/PlanService.php';
require __DIR__ . '/Stripe/Service/PriceService.php';
require __DIR__ . '/Stripe/Service/ProductService.php';
require __DIR__ . '/Stripe/Service/PromotionCodeService.php';
require __DIR__ . '/Stripe/Service/QuoteService.php';
require __DIR__ . '/Stripe/Service/Radar/EarlyFraudWarningService.php';
require __DIR__ . '/Stripe/Service/Radar/ValueListService.php';
require __DIR__ . '/Stripe/Service/Radar/ValueListItemService.php';
require __DIR__ . '/Stripe/Service/RefundService.php';
require __DIR__ . '/Stripe/Service/Reporting/ReportRunService.php';
require __DIR__ . '/Stripe/Service/Reporting/ReportTypeService.php';
require __DIR__ . '/Stripe/Service/ReviewService.php';
require __DIR__ . '/Stripe/Service/SetupAttemptService.php';
require __DIR__ . '/Stripe/Service/SetupIntentService.php';
require __DIR__ . '/Stripe/Service/Sigma/ScheduledQueryRunService.php';
require __DIR__ . '/Stripe/Service/SkuService.php';
require __DIR__ . '/Stripe/Service/SourceService.php';
require __DIR__ . '/Stripe/Service/SubscriptionService.php';
require __DIR__ . '/Stripe/Service/SubscriptionItemService.php';
require __DIR__ . '/Stripe/Service/SubscriptionScheduleService.php';
require __DIR__ . '/Stripe/Service/TaxCodeService.php';
require __DIR__ . '/Stripe/Service/TaxRateService.php';
require __DIR__ . '/Stripe/Service/Terminal/ConnectionTokenService.php';
require __DIR__ . '/Stripe/Service/Terminal/LocationService.php';
require __DIR__ . '/Stripe/Service/Terminal/ReaderService.php';
require __DIR__ . '/Stripe/Service/TokenService.php';
require __DIR__ . '/Stripe/Service/TopupService.php';
require __DIR__ . '/Stripe/Service/TransferService.php';
require __DIR__ . '/Stripe/Service/WebhookEndpointService.php';

// Service factories
require __DIR__ . '/Stripe/Service/CoreServiceFactory.php';
require __DIR__ . '/Stripe/Service/BillingPortal/BillingPortalServiceFactory.php';
require __DIR__ . '/Stripe/Service/Checkout/CheckoutServiceFactory.php';
require __DIR__ . '/Stripe/Service/Identity/IdentityServiceFactory.php';
require __DIR__ . '/Stripe/Service/Issuing/IssuingServiceFactory.php';
require __DIR__ . '/Stripe/Service/Radar/RadarServiceFactory.php';
require __DIR__ . '/Stripe/Service/Reporting/ReportingServiceFactory.php';
require __DIR__ . '/Stripe/Service/Sigma/SigmaServiceFactory.php';
require __DIR__ . '/Stripe/Service/Terminal/TerminalServiceFactory.php';

// OAuth
require __DIR__ . '/Stripe/OAuth.php';
require __DIR__ . '/Stripe/OAuthErrorObject.php';
require __DIR__ . '/Stripe/Service/OAuthService.php';

// Webhooks
require __DIR__ . '/Stripe/Webhook.php';
require __DIR__ . '/Stripe/WebhookSignature.php';
