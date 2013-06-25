class php {
  $packages = [
    "php5",
    "php5-cli",
    "php5-common",
    "php5-xdebug"
  ]
  package { $packages:
    ensure => present,
    require => Exec["apt-get update"]
  }
}
