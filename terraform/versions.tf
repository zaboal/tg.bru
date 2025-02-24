terraform {
  required_providers {
    yandex = {
      source  = "yandex-cloud/yandex"
      version = ">= 0.136.0"
    }
    archive = {
      source  = "hashicorp/archive"
      version = ">= 2.0.0"
    }
    curl = {
      source  = "marcofranssen/curl"
      version = "0.7.0"
    }
  }
  required_version = ">= 0.13"
}

provider "yandex" {
  token     = var.yandex_token
  cloud_id  = var.yandex_cloud_id
  folder_id = var.yandex_folder_id
  zone      = "ru-central1-a"
}