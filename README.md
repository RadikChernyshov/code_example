# PHP Code Examples

#### [PHP 5.6.*](https://github.com/RadikChernyshov/code_example/tree/master/php5.6.*/SocialUsersDataComponent) example
 
```
├── Controllers
│   ├── NotificationsController.php
│   └── ReportsController.php
├── Models
│   ├── MalfunctionModel.php
│   └── UserModel.php
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
├── Controllers
│   ├── LeadsController.php
│   └── StoriesCategoriesController.php
├── NotificationService
│   ├── Interfaces
│   │   ├── DbMessageInterface.php
│   │   ├── EmailMessageInterface.php
│   │   ├── MessageStrategyInterface.php
│   │   ├── QueueMessageInterface.php
│   │   └── SmsMessageInterface.php
│   ├── Message
│   │   ├── AbstractMessage.php
│   │   ├── LeadEmailVerificationMessage.php
│   │   ├── LeadPhoneVerificationMessage.php
│   │   ├── LeadVerifiedMessage.php
│   │   ├── RefoundMessage.php
│   │   ├── ResetTokenMessage.php
│   │   ├── VerificationMessage.php
│   │   └── WelcomeMessage.php
│   ├── MessageFactory.php
│   ├── NotificationListener.php
│   ├── Strategy
│   │   ├── DbStrategy.php
│   │   ├── EmailStrategy.php
│   │   ├── QueueStrategy.php
│   │   └── SmsStrategy.php
│   └── Type
│       ├── AbstractNotificationEvent.php
│       ├── LeadEmailVerificationNotificationEvent.php
│       ├── LeadPhoneNumberVerificationNotificationEvent.php
│       ├── LeadVerifiedNotificationEvent.php
│       ├── RefoundNotificationEvent.php
│       ├── ResetTokenNotificationEvent.php
│       └── WelcomeNotificationEvent.php
└── Repositories
    ├── CampaignPropertiesRepository.php
    └── CampaignRepository.php
```

#### [Refactoring Issue](https://github.com/RadikChernyshov/code_example/tree/master/refactoring_task)

```
├── README.md
├── bootstrap.php
├── cache
│   ├── flash
│   └── portal
├── composer.json
├── composer.lock
└── src
    ├── ApiCall.php
    ├── Applets
    │   ├── Applet.php
    │   └── Applets.php
    ├── Applications
    │   ├── Application.php
    │   └── Applications.php
    ├── Config.php
    ├── Exceptions
    │   ├── FileNotFoundException.php
    │   └── UndefinedException.php
    ├── Generators
    │   └── FileGenerator.php
    ├── Interfaces
    │   ├── AppletInterface.php
    │   ├── AppletsInterface.php
    │   ├── ApplicationInterface.php
    │   ├── ApplicationLanguageInterface.php
    │   ├── ApplicationsInterface.php
    │   ├── FileGeneratorInterface.php
    │   ├── LanguageBatchInterface.php
    │   └── LanguageFileInterface.php
    ├── LanguageBatchBo.php
    ├── LanguageFiles
    │   ├── AppletLanguageFile.php
    │   └── ApplicationLanguageFile.php
    └── generate_language_files.php

```