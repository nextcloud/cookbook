variable "PHPVERSION" {
  default = "8.3"
}

variable "REGISTRY" {
  default = "local"
}

target "dut" {
  tags = REGISTRY == "dockerhub" ? [
    "nextcloudcookbook/testci:php${PHPVERSION}"
  ] : REGISTRY == "ghcr" ? [
    "ghcr.io/nextcloud/cookbook/testci:php${PHPVERSION}"
  ] : ["cookbook_unittesting_dut:latest"]
  args = {
    PHPVERSION = PHPVERSION
  }
  cache-from = [
    "type=registry,ref=ghcr.io/nextcloud/cookbook/testci:php${PHPVERSION}"
  ]
  cache-to = [ "type=inline" ]
}

target "mysql" {
  tags = REGISTRY == "dockerhub" ? [
    "nextcloudcookbook/mariadb-test:latest"
  ] : REGISTRY == "ghcr" ? [
    "ghcr.io/nextcloud/cookbook/mariadb-test:latest"
  ] : ["cookbook_unittesting_mysql:latest"]
  cache-from = [
    "type=registry,ref=ghcr.io/nextcloud/cookbook/mariadb-test:latest"
  ]
  cache-to = [ "type=inline" ]
}

target "postgres" {
  tags = REGISTRY == "dockerhub" ? [
    "nextcloudcookbook/postgres-test:latest"
  ] : REGISTRY == "ghcr" ? [
    "ghcr.io/nextcloud/cookbook/postgres-test:latest"
  ] : ["cookbook_unittesting_postgres:latest"]
  cache-from = [
    "type=registry,ref=ghcr.io/nextcloud/cookbook/postgres-test:latest"
  ]
  cache-to = [ "type=inline" ]
}

target "www" {
  tags = [
    "cookbook_unittesting_www:latest",
  ]
}
