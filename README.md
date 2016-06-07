# Localize

We believe that everybody should be able to use software in their own language.

Localize takes care of all the background work. You can concentrate on great apps and perfect translations.

Invite staff members to collaborate with assigned roles, let users contribute and export translations in seconds.

> If you talk to a man in a language he understands, that goes to his head. If you talk to him in his language, that goes to his heart.
> — *Nelson Mandela*

**Live website:** [www.localize.im](https://www.localize.im/)

## Requirements

 * Apache HTTP Server 2.2.0+
 * PHP 5.3.0+
   * `mbstring` extension
 * MySQL 5.5.3+ **or** MariaDB 5.5.23+

## Installation

 1. Create a new MySQL or MariaDB database
 1. Import the tables from [`database/structure.sql`](database/structure.sql) into the database
 1. Rename `config.example.php` to `config.php` and fill in proper values

## Contributing

We welcome any contribution, no matter how small or large. Please fork this repository, apply your changes, and submit your contributions by sending a pull request.

## Security

[Disclose bugs and vulnerabilities](http://security.localize.im/) or read more about [security](SECURITY.md).

## License

```
Copyright (c) delight.im <info@delight.im>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see {http://www.gnu.org/licenses/}.
```
