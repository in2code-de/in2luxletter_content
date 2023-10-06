# TYPO3 Extension `in2luxletter_content`

This extensions makes it possible to use user(group)-specific content in LUXletter.

In conjunction with `asynchronousQueueStorage` enabled in EXT:luxletter, this extension
enables content that is dependent on the recipient (FE user). Depending on the extension
setting, the content is crawled per usergroup combination or per user (Caution! High
load on the server!).

⚠️ **Development is still in progress! Use in production with care.** ⚠️

## Installation

Install this extension via composer by using the following command:

```bash
composer require in2code/in2luxletter-content
```
