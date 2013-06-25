class bootstrap { 
  group { 'puppet':
    ensure => 'present'
  }
  exec { 'apt-get update':
    command => '/usr/bin/apt-get update'
  }
  package { 'make':
    ensure => present,
    require => Exec["apt-get update"]
  }
  package { 'curl':
    ensure => present,
    require => Exec["apt-get update"]
  }
}
