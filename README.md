# PHP Code Examples

#### [PHP 5.6.*](https://github.com/RadikChernyshov/code_example/tree/master/php5.6.*/SocialUsersDataComponent) example
 
```$xslt
└── SocialUsersDataComponent
    ├── SocialComponent.php
    ├── interfaces
    │   └── SocialProviderInterface.php
    └── providers
        ├── AbstractProvider.php
        ├── FacebookProvider.php
        └── GoogleProvider.php
``` 
 
#### [PHP 7.1.*](https://github.com/RadikChernyshov/code_example/tree/master/php7.1.*/NotificationService) example

```
└── NotificationService
    ├── Interfaces
    │   ├── DbMessageInterface.php
    │   ├── EmailMessageInterface.php
    │   ├── MessageStrategyInterface.php
    │   ├── QueueMessageInterface.php
    │   └── SmsMessageInterface.php
    ├── Message
    │   ├── AbstractMessage.php
    │   ├── LeadEmailVerificationMessage.php
    │   ├── LeadPhoneVerificationMessage.php
    │   ├── LeadVerifiedMessage.php
    │   ├── RefoundMessage.php
    │   ├── ResetTokenMessage.php
    │   ├── VerificationMessage.php
    │   └── WelcomeMessage.php
    ├── MessageFactory.php
    ├── NotificationListener.php
    ├── Strategy
    │   ├── DbStrategy.php
    │   ├── EmailStrategy.php
    │   ├── QueueStrategy.php
    │   └── SmsStrategy.php
    └── Type
        ├── AbstractNotificationEvent.php
        ├── LeadEmailVerificationNotificationEvent.php
        ├── LeadPhoneNumberVerificationNotificationEvent.php
        ├── LeadVerifiedNotificationEvent.php
        ├── RefoundNotificationEvent.php
        ├── ResetTokenNotificationEvent.php
        └── WelcomeNotificationEvent.php
```