---
permalink: docs/spiders.html
---

# Spiders

The spiders are classes which implement the functionality which is needed to crawl a certain or group of sites.
Spiders must implement the interface `Schrapert\SpiderInterface`. This interface implements the follow methods:

1. `getName` this method should return a string, this name is used for several things such as logging.
2. `parse` this method receives a response in the form of a `ResponseInterface` and should extract items and/or next requests.
3. `startRequests` this method should return an iterable of `Request` objects or url's to crawl.

