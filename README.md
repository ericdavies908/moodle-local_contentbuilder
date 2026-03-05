# Section Content Builder (`local_contentbuilder`)

A Moodle local plugin for the University of Glasgow Learning Innovation Support Unit (LISU) that provides a structured block-based content builder for pushing styled HTML into Moodle course pages and section summaries.

## Features

- Build rich content from typed blocks: Text, Image + Text, Full-width image, Callout box
- Author-controlled heading levels (H3–H6)
- Image upload via Moodle file manager (stored in Moodle file system)
- H5P embeds via the standard TinyMCE toolbar
- Push content as a new Page activity or directly into a section summary
- Up to 10 blocks per push
- Trailing editable space appended so pages remain manually editable after push

## Requirements

- Moodle 5.0 or later
- PHP 8.1 or later

## Installation

1. Copy or upload the `local_contentbuilder` folder to `{moodleroot}/local/`.
2. Log in as administrator and visit **Site administration → Notifications** to complete installation.
3. No database tables are created.

## Usage

Editing teachers and managers can access the tool from the course **Administration** menu under **Section Content Builder**.

## Capabilities

| Capability | Risk | Default |
|---|---|---|
| `local/contentbuilder:pushcontent` | RISK_XSS, RISK_SPAM | Editing teacher, Manager |

## License

GNU GPL v3 or later — https://www.gnu.org/copyleft/gpl.html

Copyright 2026 University of Glasgow LISU
