# Drupal Module Development

This repository contains custom Drupal modules developed from scratch as part of an internship program at Etiya. The project focuses on data integration, content importing, and event management on the Drupal CMS, developed collaboratively with a team.

These modules are designed to fetch data from external services, streamline content importation, and handle event registration processes in alignment with corporate requirements.

## Developed Modules

The project consists of three main modules that complement each other:

### 1. Custom Phone Importer (`custom_phone_importer`)
A data integration module that fetches phone and contact information from external services and securely integrates it into the Drupal system. It ensures that data coming from external sources is processed and stored systematically.

### 2. News Importer (`news_importer`)
An integration module that periodically retrieves news content from various external sources or APIs and automatically maps and imports them into the corresponding content types within Drupal.

### 3. Event Registration (`event_registration`)
A module dedicated to managing the end-to-end event registration workflow. It handles attendee registrations, quota management, and stores event-related data in accordance with corporate business rules.

## Key Features
* **External Service Integration:** Secure data communication and retrieval from external APIs and RESTful services.
* **Automated Content Import:** Processing raw data and converting it into Drupal nodes and entities for seamless integration.
* **Custom Registration Workflows:** Dynamic event-based registration forms and comprehensive attendee management.

## Technologies & Environment
* **Platform:** Drupal CMS
* **Language:** PHP
* **Core APIs:** REST API, Drupal Entity API, Form API

## Installation and Usage

1. Clone this repository:
   ```
   git clone [https://github.com/mehmetgokgul/Drupal-Module-Development.git](https://github.com/mehmetgokgul/Drupal-Module-Development.git)
2. Move the module folders (custom_phone_importer, news_importer, event_registration) to the web/modules/custom directory of your Drupal project.

3. Enable the modules via the Drupal administrative interface (Admin -> Extend) or by using Drush:
```
drush en custom_phone_importer news_importer event_registration -y
```
4. Configure the necessary service endpoints and settings through the Drupal configuration interface.

## Developers
This project was developed by Mehmet Gökgül and team members during the Etiya internship program.
