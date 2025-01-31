terraform {
  required_providers {
    yandex = {
      source  = "yandex-cloud/yandex"
      version = ">= 0.136.0"
    }
  }
  required_version = ">= 0.13"
}

provider "yandex" {
  token     = ""
  cloud_id  = ""
  folder_id = ""
  zone      = "ru-central1-a"
}

resource "yandex_function" "my_function" {
  name        = "terraform-function"
  user_hash   = "1"
  description = "For tg"
  runtime     = "php82"
  entrypoint  = "index.handler"
  memory      = "128"
  execution_timeout = "10"

  content {
    zip_filename = "archive.zip"
  }

  environment = {
  }
}
