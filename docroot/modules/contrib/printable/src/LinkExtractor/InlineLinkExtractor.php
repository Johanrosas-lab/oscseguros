<?php

/**
 * @file
 * Contains \Drupal\printable\LinkExtractor\InlineLinkExtractor.
 */

namespace Drupal\printable\LinkExtractor;

use Drupal\printable\LinkExtractor\LinkExtractorInterface;
use wa72\htmlpagedom\HtmlPageCrawler;
use Drupal\Core\Url;

/**
 * Link extractor.
 */
class InlineLinkExtractor implements LinkExtractorInterface {

  /**
   * The DomCrawler object.
   *
   * @var \Wa72\HtmlPageDom\HtmlPageCrawler
   */
  protected $crawler;

  /**
   * Constructs a new InlineLinkExtractor object.
   */
  public function __construct(HtmlPageCrawler $crawler) {
    $this->crawler = $crawler;
  }

  /**
   * {@inheritdoc}
   */
  public function extract($string) {
    $this->crawler->addContent($string);

    $this->crawler->filter('a')->each(function(HtmlPageCrawler $anchor, $uri) {
      $href = $anchor->attr('href');
      $url = $this->urlFromHref($href);
      $anchor->append(' (' . $url->toString() . ')');
    });

    return (string) $this->crawler;
  }

  /**
   * {@inheritdoc}
   */
  public function removeAttribute($content, $attr) {
    $this->crawler->addContent($content);
    $this->crawler->filter('a')->each(function(HtmlPageCrawler $anchor, $uri) {
      $anchor->removeAttribute('href');
    });
    return (string) $this->crawler;
  }

  /**
   * {@inheritdoc}
   */
  public function listAttribute($content) {
    $this->crawler->addContent($content);
    $this->links = array();
    $this->crawler->filter('a')->each(function(HtmlPageCrawler $anchor, $uri) {
      global $base_url;

      $href = $anchor->attr('href');
      try {
        $this->links[] = $base_url . \Drupal::service('path.alias_manager')->getAliasByPath($href);
      } catch (\Exception $e) {
        $this->links[] = $this->urlFromHref($href)->toString();
      }
    });
    $this->crawler->remove();
    return implode(',', $this->links);
  }

  /**
   * Generate a URL object given a URL from the href attribute.
   *
   * Tries external URLs first, if that fails it will attempt
   * generation from a relative URL.
   *
   * @param string $href
   *   The URL from the href attribute.
   *
   * @return \Drupal\Core\Url
   *   The created URL object.
   */
  private function urlFromHref($href) {
    try {
      $url = Url::fromUri($href, array('absolute' => TRUE));
    }
    catch (\InvalidArgumentException $e) {
      $url = Url::fromUserInput($href, array('absolute' => TRUE));
    }

    return $url;
  }

}
