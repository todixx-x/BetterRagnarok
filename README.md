<p align="center">
	<img src="https://github.com/todixx-x/BetterRagnarok/blob/master/.github/readme/banner.png" alt="The BetterRagnarok logo" title="BetterRagnarok" loading="eager" />
</p>

<p align="center">
	<b>Lightweight, OOP based Minecraft: Bedrock Edition server software written in PHP,<br>themed with a distinctive purple terminal experience.</b>
</p>

<p align="center">
	<a href="https://github.com/todixx-x/BetterRagnarok/actions/workflows/ci.yml"><img src="https://github.com/todixx-x/BetterRagnarok/actions/workflows/ci.yml/badge.svg" alt="CI" /></a>
	<a href="https://github.com/todixx-x/BetterRagnarok/releases/latest"><img alt="GitHub release (latest SemVer)" src="https://img.shields.io/github/v/release/todixx-x/BetterRagnarok?label=release&sort=semver"></a>
	<a href="https://github.com/todixx-x/BetterRagnarok/releases"><img alt="GitHub all releases" src="https://img.shields.io/github/downloads/todixx-x/BetterRagnarok/total?label=downloads%40total"></a>
	<a href="https://github.com/todixx-x/BetterRagnarok/releases/latest"><img alt="GitHub release (latest by SemVer)" src="https://img.shields.io/github/v/release/todixx-x/BetterRagnarok?label=release&sort=semver"></a>
</p>

---

## What is BetterRagnarok?

BetterRagnarok is [PocketMine-MP](https://pmmp.io) reimagined. It started as a fork of PocketMine and Altay, then was re-themed from the ground up with a purple terminal identity, a tighter default experience, and a few opinionated extras the PocketMine base doesn't always ship with.

If you want a Bedrock server you can actually make your own — fast, scriptable, and pleasant to live in — this is it.

### Why this fork?

- 💜 **Purple, on purpose.** The console, banner, and logs are themed end-to-end. No more defaulting to grayscale when something explodes.
- 🧩 **Real plugin power.** A full plugin API with hooks across the server. Drop a `.phar` in `plugins/` and you're running.
- 🗺️ **Multi-world, no extra boxes.** Spawn, survival, minigames — separate worlds on one node, no transferring players around.
- 🏎️ **Fast enough for 100+ players.** Depending on hardware and plugins, it just runs.
- ⤴️ **Up to date.** New Minecraft versions are usually supported within days.
- 🛠️ **Hackable.** Clean OOP, predictable internals, and a build pipeline that lets you patch and ship.

---

## Getting started

Grab the latest release from the [releases page](https://github.com/todixx-x/BetterRagnarok/releases/latest), drop the phar on a machine with PHP 8.1+ and the right extensions, and run it. That's the whole pitch.

```bash
curl -sL https://github.com/todixx-x/BetterRagnarok/releases/latest/download/BetterRagnarok.phar -o BetterRagnarok.phar
php BetterRagnarok.phar
```

If you want to build it yourself instead, see [BUILDING.md](BUILDING.md).

### Requirements

- PHP 8.1 or newer, 64-bit
- The extension list in `composer.json` (`ext-chunkutils2`, `ext-crypto`, `ext-leveldb`, `ext-pmmpthread`, `ext-yaml`, plus the usual suspects)
- A POSIX-ish environment for the best experience

---

## Community & support

The Bedrock server world lives on the PocketMine Discord — it's the same plugin ecosystem, the same docs, and the same people. Drop in.

- 💬 [PocketMine Discord](https://discord.gg/PocketMine) — community, dev chat, support
- 🧵 [StackOverflow](https://stackoverflow.com/tags/pocketmine) under the `pocketmine` tag
- 🐞 [GitHub Issues](https://github.com/todixx-x/BetterRagnarok/issues) for bugs in this fork specifically

Please report **security vulnerabilities** through the [Security tab](https://github.com/todixx-x/BetterRagnarok/security), not the public issue tracker. See [SECURITY.md](SECURITY.md).

---

## Writing plugins

BetterRagnarok uses the PocketMine plugin API, so anything that works on PocketMine-MP works here. The plugin ecosystem is large and the docs are decent.

- 📚 [Developer documentation](https://devdoc.pmmp.io) — the general PocketMine plugin guide
- 📖 [API reference (bleeding edge)](https://apidoc-dev.pmmp.io) — Doxygen output, regenerated weekly
- 🧪 [ExamplePlugin](https://github.com/pmmp/ExamplePlugin/) — minimal plugin showing the basics
- 🛠️ [DevTools](https://github.com/pmmp/DevTools/) — pack/unpack phars, generate skeletons, check permissions

If you're stuck, ask in Discord. Someone has hit the same wall before.

---

## Contributing

Pull requests welcome. Before you open one, skim:

- [BUILDING.md](BUILDING.md) — build & run from source
- [CONTRIBUTING.md](CONTRIBUTING.md) — conventions, style, and PR expectations

CI runs on every push. Keep it green.

---

## License

LGPL-3.0. See [LICENSE](LICENSE).

BetterRagnarok is a fork of PocketMine-MP / Altay and is not affiliated with Mojang. All brands and trademarks belong to their respective owners. BetterRagnarok is not a Mojang-approved software, nor is it associated with Mojang.
