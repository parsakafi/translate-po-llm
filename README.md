# Translating .po file using LLM

Here is an example of using a Large Language Model (LLM) to translate a .po file.

I utilize [LM Studio](https://lmstudio.ai) along with the [MistralAI](https://mistral.ai) model to translate .po files
from a WordPress
plugin.

## How to use:

1. First download or clone this repository
2. Install [Composer](https://getcomposer.org) requirements (Execute this command in your terminal application within
   the project directory)

```sh
  composer install
  ```

3. Copy .po file to project dir and renamed to translate-file.po or change name in config variable.
4. Set your config in `$config` variable in `translate.php` file. Using LLM on your computer or use online API (This
   serves as a basic example, and there is a strong likelihood of encountering bugs in various scenarios.)
5. Run PHP script in your browser or terminal

```sh
  php translate.php
  ```
