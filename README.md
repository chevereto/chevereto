# Chevereto: Ultimate image and video sharing software

<p align="center">
    <a href="https://chevereto.com"><img alt="Chevereto" src="chevereto.svg" width="80%"></a>
</p>

[![Chevereto](.github/banner/chevereto-ultimate-remix.png)](https://chevereto.com)

[![Chevereto Docs](https://img.shields.io/badge/chevereto-docs-50C878?style=flat-square)](https://v4-docs.chevereto.com/)
[![Chevereto Community](https://img.shields.io/badge/chevereto-community-blue?style=flat-square)](https://chevereto.com/community)
[![Chevereto Discord](https://img.shields.io/badge/chevereto-discord-5865F2?style=flat-square)](https://chevereto.com/go/discord)
[![Chevereto Demo](https://img.shields.io/badge/chevereto-demo-d4af37?style=flat-square)](https://demo.chevereto.com)
[![AGPL-3.0-only](https://img.shields.io/github/license/chevereto/chevereto?style=flat-square)](LICENSE)
[![Legacy stars](https://img.shields.io/github/stars/rodber/chevereto-free?style=flat-square&logo=github&label=Legacy%20stars&color=red)](https://github.com/rodber/chevereto-free)
[![Awesome F/OSS](https://img.shields.io/badge/Awesome_F%2FOSS-Certified-black?colorA=&colorB=874efe&style=flat-square)](https://awsmfoss.com/chevereto/)

> 🔔 [Subscribe](https://chv.to/newsletter) to don't miss any update regarding Chevereto.

Chevereto enables to create a media sharing website on your own server. It's your hosting and your rules, say goodbye to closures and restrictions. ⭐️ [Live demo](https://demo.chevereto.com)

Chevereto is a turnkey system which main use case is to provide a self-hosted platform for content creators, communities and businesses. It's features are all about media sharing, with a strong focus on user experience, privacy and security. On its pro edition Chevereto excels as a content management system with heavy business related features that you won't get on other systems.

![screen](.github/screen/user-profile.jpeg)

## Install

Chevereto runs anywhere, system requirements are minimal and it can run on any server.

Install Chevereto following our guides for:

* [Docker](https://github.com/chevereto/docker) (Multi-arch image compatible with `x86_64` and `arm64`)
  * [Pure Docker](https://v4-docs.chevereto.com/guides/docker/pure-docker)
  * [Chevereto Docker](https://v4-docs.chevereto.com/guides/docker/)
* [VPS](https://v4-docs.chevereto.com/guides/server/vps) (DigitalOcean, Linode, Vultr, etc)
* [cPanel](https://v4-docs.chevereto.com/guides/cpanel/)
* [Plesk](https://v4-docs.chevereto.com/guides/plesk/)

Chevereto is also available at [DigitalOcean Marketplace](https://chevereto.com/go/digitalocean), [Vultr Marketplace](https://chevereto.com/go/vultr), [Installatron](https://installatron.com/chevereto) and [Softaculous](https://www.softaculous.com/apps/galleries/Chevereto). Review our [Installation docs](https://v4-docs.chevereto.com/application/installing/installation.html) for all alternatives.

## Updating

For Chevereto V4 users:

* Follow the [Updating guide](https://v4-docs.chevereto.com/application/installing/updating.html) to keep your Chevereto V4 system updated.

## Upgrading

For Chevereto V3 users:

* Follow the [Upgrading guide](https://v4-docs.chevereto.com/application/installing/upgrading.html) to upgrade to Chevereto V4.
* Check the [Welcome Back](https://v4-docs.chevereto.com/introduction/changelog/welcome-back.html#from-chevereto-v3) reference.

## Files supported

With Chevereto you can upload and share the following media types from device file browser, drag and drop, on-the-fly device camera (video and photos), clipboard, URL, ShareX and via API.

* image/jpeg
* image/gif
* image/png
* image/webp
* image/bmp
* video/quicktime
* video/mp4
* video/webm

## Documentation

Chevereto is so **feature-rich**, mature and robust that we need three layers of documentation for it. The Chevereto software project started on **2007** and it has been actively maintained since then.

* [Documentation](https://v4-docs.chevereto.com)
* [User manual](https://v4-user.chevereto.com/)
* [Admin manual](https://v4-admin.chevereto.com/)

## Features

**Note:** This is the repository for Chevereto free edition. This software is intended for **personal usage** as it doesn't contain [all the features](https://chevereto.com/features) of commercial editions. This is a short, not exhaustive, list of features available on Chevereto editions. Feel free to request a free demo of the pro edition at [chevereto.com](https://chevereto.com) to see all the features in action.

### Uploading features

| Feature                                  | Free  | Lite  |  Pro  |
| ---------------------------------------- | :---: | :---: | :---: |
| Image & Video uploads                    |   ✅   |   ✅   |   ✅   |
| JPEG PNG BMP GIF WEBP MOV MP4 WEBM       |   ✅   |   ✅   |   ✅   |
| ShareX support                           |   ✅   |   ✅   |   ✅   |
| 360° images                              |   ✅   |   ✅   |   ✅   |
| Strip image EXIF data                    |   ✅   |   ✅   |   ✅   |
| Clipboard upload                         |   ✅   |   ✅   |   ✅   |
| Drag-and-drop upload                     |   ✅   |   ✅   |   ✅   |
| File delete link                         |   ✅   |   ✅   |   ✅   |
| Time-based expirable uploads             |   ✅   |   ✅   |   ✅   |
| Thumbs & medium sized images             |   ✅   |   ✅   |   ✅   |
| Video frame image                        |   ✅   |   ✅   |   ✅   |
| Duplicate media detection                |   ✅   |   ✅   |   ✅   |
| Auto file naming options                 |   ✅   |   ✅   |   ✅   |
| Storage modes (date, direct)             |   ✅   |   ✅   |   ✅   |
| Upload user interface (container, route) |   ✅   |   ✅   |   ✅   |
| Upload plugin (PUP.js)                   |   –   |   ✅   |   ✅   |
| Watermark image uploads                  |   –   |   –   |   ✅   |
| Upload moderation                        |   –   |   –   |   ✅   |
| External storage servers                 |   –   |   –   |   ✅   |
| Bulk content importer                    |   –   |   –   |   ✅   |

### Sharing features

| Feature                        | Free  | Lite  |  Pro  |
| ------------------------------ | :---: | :---: | :---: |
| Direct link sharing            |   ✅   |   ✅   |   ✅   |
| Sharing button                 |   ✅   |   ✅   |   ✅   |
| Media oEmbed                   |   ✅   |   ✅   |   ✅   |
| HTML, Markdown & BBCode        |   ✅   |   ✅   |   ✅   |
| Embed codes on upload complete |   ✅   |   ✅   |   ✅   |
| Embed codes on selected media  |   ✅   |   ✅   |   ✅   |
| Embed codes media page         |   ✅   |   ✅   |   ✅   |

### User features

| Feature                     | Free  | Lite  |  Pro  |
| --------------------------- | :---: | :---: | :---: |
| User profiles               |   ✅   |   ✅   |   ✅   |
| Private user profiles       |   ✅   |   ✅   |   ✅   |
| User-based API              |   ✅   |   ✅   |   ✅   |
| Multiple users & management |   –   |   ✅   |   ✅   |
| Guest API                   |   –   |   ✅   |   ✅   |

### Social features

| Feature                      | Free  | Lite  |  Pro  |
| ---------------------------- | :---: | :---: | :---: |
| Call-to-action album buttons |   ✅   |   ✅   |   ✅   |
| Random button                |   ✅   |   ✅   |   ✅   |
| Notifications                |   –   |   ✅   |   ✅   |
| List users                   |   –   |   ✅   |   ✅   |
| Followers                    |   –   |   –   |   ✅   |
| Likes                        |   –   |   –   |   ✅   |

### Organization features

| Feature                           | Free  | Lite  |  Pro  |
| --------------------------------- | :---: | :---: | :---: |
| Albums & Sub-albums               |   ✅   |   ✅   |   ✅   |
| Categories                        |   ✅   |   ✅   |   ✅   |
| Search                            |   ✅   |   ✅   |   ✅   |
| Media & Album listings            |   ✅   |   ✅   |   ✅   |
| Configurable list items per page  |   ✅   |   ✅   |   ✅   |
| Classic + Endless scroll listings |   ✅   |   ✅   |   ✅   |
| Listing viewer                    |   ✅   |   ✅   |   ✅   |
| Image listing size (fixed, fluid) |   ✅   |   ✅   |   ✅   |
| Album listing requirement         |   ✅   |   ✅   |   ✅   |
| Listing columns per device        |   ✅   |   ✅   |   ✅   |
| Explore & Discovery               |   –   |   ✅   |   ✅   |
| Advanced search                   |   –   |   ✅   |   ✅   |

### Security features

| Feature                         | Free  | Lite  |  Pro  |
| ------------------------------- | :---: | :---: | :---: |
| Two-Factor Authentication (2FA) |   ✅   |   ✅   |   ✅   |
| Encrypt secrets                 |   ✅   |   ✅   |   ✅   |
| Crypt-salted IDs                |   ✅   |   ✅   |   ✅   |
| IP banning                      |   –   |   –   |   ✅   |
| Stop words                      |   –   |   –   |   ✅   |

### Admin features

| Feature                                                                                       | Free  | Lite  |  Pro  |
| --------------------------------------------------------------------------------------------- | :---: | :---: | :---: |
| Dashboard (admin UI)                                                                          |   ✅   |   ✅   |   ✅   |
| System stats & usage                                                                          |   ✅   |   ✅   |   ✅   |
| Website name                                                                                  |   ✅   |   ✅   |   ✅   |
| Website doctitle                                                                              |   ✅   |   ✅   |   ✅   |
| Website description                                                                           |   ✅   |   ✅   |   ✅   |
| Website privacy mode (public, private)                                                        |   ✅   |   ✅   |   ✅   |
| Default timezone                                                                              |   ✅   |   ✅   |   ✅   |
| Uploadable file extensions                                                                    |   ✅   |   ✅   |   ✅   |
| Guest uploads auto delete                                                                     |   ✅   |   ✅   |   ✅   |
| Upload threads                                                                                |   ✅   |   ✅   |   ✅   |
| Upload maximum image size                                                                     |   ✅   |   ✅   |   ✅   |
| Upload Exif removal                                                                           |   ✅   |   ✅   |   ✅   |
| Upload max file size (users and guest)                                                        |   ✅   |   ✅   |   ✅   |
| Upload path                                                                                   |   ✅   |   ✅   |   ✅   |
| Upload file naming                                                                            |   ✅   |   ✅   |   ✅   |
| Upload thumb size                                                                             |   ✅   |   ✅   |   ✅   |
| Upload medium size and dimension                                                              |   ✅   |   ✅   |   ✅   |
| Semantics                                                                                     |   ✅   |   ✅   |   ✅   |
| Default palette                                                                               |   ✅   |   ✅   |   ✅   |
| Default font                                                                                  |   ✅   |   ✅   |   ✅   |
| Image load max file size                                                                      |   ✅   |   ✅   |   ✅   |
| Image first tab                                                                               |   ✅   |   ✅   |   ✅   |
| Embed codes (content)                                                                         |   ✅   |   ✅   |   ✅   |
| Custom JS & CSS                                                                               |   ✅   |   ✅   |   ✅   |
| Universal CDN support                                                                         |   ✅   |   ✅   |   ✅   |
| [Default language](https://v4-admin.chevereto.com/settings/languages.html#default-language)   |   ✅   |   ✅   |   ✅   |
| Homepage style                                                                                |   –   |   ✅   |   ✅   |
| Homepage cover images                                                                         |   –   |   ✅   |   ✅   |
| Homepage title & paragraph                                                                    |   –   |   ✅   |   ✅   |
| Homepage call to action                                                                       |   –   |   ✅   |   ✅   |
| Pages                                                                                         |   –   |   ✅   |   ✅   |
| Lock NSFW editing                                                                             |   –   |   ✅   |   ✅   |
| User min age required                                                                         |   –   |   ✅   |   ✅   |
| User avatar max file size                                                                     |   –   |   ✅   |   ✅   |
| User background max file size                                                                 |   –   |   ✅   |   ✅   |
| Guest API key                                                                                 |   –   |   ✅   |   ✅   |
| [Enabled languages](https://v4-admin.chevereto.com/settings/languages.html#enabled-languages) |   –   |   –   |   ✅   |
| Hide "Powered by Chevereto"                                                                   |   –   |   –   |   ✅   |
| Logo & branding                                                                               |   –   |   –   |   ✅   |
| Logo type (vector, image, text)                                                               |   –   |   –   |   ✅   |
| Logo height                                                                                   |   –   |   –   |   ✅   |
| Logo favicon image                                                                            |   –   |   –   |   ✅   |
| Routing (user, image, album)                                                                  |   –   |   –   |   ✅   |
| Routing root                                                                                  |   –   |   –   |   ✅   |
| External services                                                                             |   –   |   –   |   ✅   |
| Comments API (Disqus, JS)                                                                     |   –   |   –   |   ✅   |
| Analytics code                                                                                |   –   |   –   |   ✅   |
| Akismet spam protection                                                                       |   –   |   –   |   ✅   |
| StopForumSpam spam protection                                                                 |   –   |   –   |   ✅   |
| CAPTCHA (reCAPTCHA, hCaptcha)                                                                 |   –   |   –   |   ✅   |
| CAPTCHA threshold                                                                             |   –   |   –   |   ✅   |
| Project Arachnid                                                                              |   –   |   –   |   ✅   |
| ModerateContent (auto approve, block, flag)                                                   |   –   |   –   |   ✅   |
| OAuth2 login providers (Amazon, Google, Discord, etc)                                         |   –   |   –   |   ✅   |
| Banners                                                                                       |   –   |   –   |   ✅   |
| Watermark uploads (guest, user, admin)                                                        |   –   |   –   |   ✅   |
| Watermark file toggles                                                                        |   –   |   –   |   ✅   |
| Watermark size requirement                                                                    |   –   |   –   |   ✅   |
| Watermark custom image                                                                        |   –   |   –   |   ✅   |
| Watermark position                                                                            |   –   |   –   |   ✅   |
| Watermark percentage                                                                          |   –   |   –   |   ✅   |
| Watermark margin                                                                              |   –   |   –   |   ✅   |
| Watermark opacity                                                                             |   –   |   –   |   ✅   |

### Admin toggles

| Feature                                                                                     | Free  | Lite  |  Pro  |
| ------------------------------------------------------------------------------------------- | :---: | :---: | :---: |
| Search (users and guest)                                                                    |   ✅   |   ✅   |   ✅   |
| Explore (users and guest)                                                                   |   ✅   |   ✅   |   ✅   |
| Random (users and guest)                                                                    |   ✅   |   ✅   |   ✅   |
| NSFW listings                                                                               |   ✅   |   ✅   |   ✅   |
| Blur NSFW content                                                                           |   ✅   |   ✅   |   ✅   |
| NSFW on random mode                                                                         |   ✅   |   ✅   |   ✅   |
| Banners on NSFW                                                                             |   ✅   |   ✅   |   ✅   |
| Uploads (users and guest)                                                                   |   ✅   |   ✅   |   ✅   |
| Uploads (URL)                                                                               |   ✅   |   ✅   |   ✅   |
| Upload moderation                                                                           |   ✅   |   ✅   |   ✅   |
| Upload embed codes                                                                          |   ✅   |   ✅   |   ✅   |
| Upload redirection                                                                          |   ✅   |   ✅   |   ✅   |
| Upload duplication                                                                          |   ✅   |   ✅   |   ✅   |
| Upload expiration                                                                           |   ✅   |   ✅   |   ✅   |
| Upload NSFW checkbox                                                                        |   ✅   |   ✅   |   ✅   |
| Download button                                                                             |   ✅   |   ✅   |   ✅   |
| Right click                                                                                 |   ✅   |   ✅   |   ✅   |
| Show Exif data                                                                              |   ✅   |   ✅   |   ✅   |
| Social share buttons                                                                        |   ✅   |   ✅   |   ✅   |
| Automatic updates check                                                                     |   ✅   |   ✅   |   ✅   |
| Dump update query                                                                           |   ✅   |   ✅   |   ✅   |
| Debug errors                                                                                |   ✅   |   ✅   |   ✅   |
| Consent screen (age gate)                                                                   |   –   |   ✅   |   ✅   |
| User sign up                                                                                |   –   |   ✅   |   ✅   |
| User content delete                                                                         |   –   |   ✅   |   ✅   |
| User notify sign up                                                                         |   –   |   ✅   |   ✅   |
| User email confirmation                                                                     |   –   |   ✅   |   ✅   |
| User email for social login                                                                 |   –   |   ✅   |   ✅   |
| [Auto language](https://v4-admin.chevereto.com/settings/languages.html#auto-language)       |   –   |   –   |   ✅   |
| [Language chooser](https://v4-admin.chevereto.com/settings/languages.html#language-chooser) |   –   |   –   |   ✅   |
| SEO URLs (media and album)                                                                  |   –   |   –   |   ✅   |
| Cookie law compliance                                                                       |   –   |   –   |   ✅   |
| Flood protection                                                                            |   –   |   –   |   ✅   |
| Flood protection notify                                                                     |   –   |   –   |   ✅   |
| Watermarks                                                                                  |   –   |   –   |   ✅   |

### System features

| Feature                                            | Free  |         Lite          |          Pro          |
| -------------------------------------------------- | :---: | :-------------------: | :-------------------: |
| Roles available                                    | admin | admin, manager & user | admin, manager & user |
| Image handling GD & ImageMagick                    |   ✅   |           ✅           |           ✅           |
| Theme palettes (10)                                |   ✅   |           ✅           |           ✅           |
| One-click upgrade (web & CLI)                      |   ✅   |           ✅           |           ✅           |
| Maintenance mode                                   |   ✅   |           ✅           |           ✅           |
| Email SMTP + phpmail()                             |   ✅   |           ✅           |           ✅           |
| Decode ID                                          |   ✅   |           ✅           |           ✅           |
| Encode ID                                          |   ✅   |           ✅           |           ✅           |
| Test-email                                         |   ✅   |           ✅           |           ✅           |
| Export user                                        |   ✅   |           ✅           |           ✅           |
| Regenerate external storage stats                  |   ✅   |           ✅           |           ✅           |
| Migrate external storage records                   |   ✅   |           ✅           |           ✅           |
| Docker support                                     |   ✅   |           ✅           |           ✅           |
| CLI console                                        |   ✅   |           ✅           |           ✅           |
| Built-in debugger ([xrDebug](https://xrdebug.com)) |   ✅   |           ✅           |           ✅           |
| Built-in REPL (PsySH)                              |   ✅   |           ✅           |           ✅           |
| Supports Tinkerwel REPL                            |   ✅   |           ✅           |           ✅           |
| Queue handling                                     |   –   |           –           |           ✅           |

## License

### Open Source license

Copyright [Rodolfo Berríos Arce](http://rodolfoberrios.com) - [AGPLv3](LICENSE).

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program. If not, see [GNU Licenses](http://www.gnu.org/licenses/).

### Commercial license

The commercial license is designed to for you to use Chevereto in commercial products and applications, without the provisions of the AGPLv3. With the commercial license, your code is kept proprietary, to yourself. See the Chevereto Commercial License at [Chevereto License](https://chevereto.com/license)

### Compare licenses

Chevereto free edition is licensed under AGPLv3, which means that you can use it for free as long as you comply with the AGPLv3 terms. If you modify the code and distribute it, you must provide the source code to the users.

**Chevereto Lite** and **Chevereto Pro** editions are released under the **Chevereto License**, which is proprietary and it can be used for commercial purposes.
