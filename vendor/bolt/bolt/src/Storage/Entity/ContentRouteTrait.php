<?php

namespace Bolt\Storage\Entity;

use Bolt\Configuration\ResourceManager;

/**
 * Trait class for ContentType routing.
 *
 * This is a breakout of the old Bolt\Content class and serves two main purposes:
 *   * Maintain backward compatibility for Bolt\Content through the remainder of
 *     the 2.x development/release life-cycle
 *   * Attempt to break up former functionality into sections of code that more
 *     resembles Single Responsibility Principles
 *
 * These traits should be considered transitional, the functionality in the
 * process of refactor, and not representative of a valid approach.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
trait ContentRouteTrait
{
    /**
     * Creates a link to EDIT this record, if the user is logged in.
     *
     * @return string
     */
    public function editlink()
    {
        $perm = 'contenttype:' . $this->contenttype['slug'] . ':edit:' . $this->id;

        if ($this->app['users']->isAllowed($perm)) {
            return $this->app->generatePath('editcontent', ['contenttypeslug' => $this->contenttype['slug'], 'id' => $this->id ]);
        } else {
            return false;
        }
    }

    /**
     * Creates a URL for the content record.
     *
     * @return string
     */
    public function link()
    {
        if (empty($this->app)) {
            $this->app = ResourceManager::getApp();
        }

        if (empty($this->id)) {
            return null;
        }

        // No links for records that are 'viewless'
        if (isset($this->contenttype['viewless']) && $this->contenttype['viewless'] == true) {
            return null;
        }

        list($binding, $route) = $this->getRoute();

        if (!$route) {
            return null;
        }

        $slug = $this->getLinkSlug();
        $link = $this->app['url_generator']->generate(
            $binding,
            array_filter(
                array_merge(
                    $route['defaults'] ?: [],
                    $this->getRouteRequirementParams($route),
                    [
                        'contenttypeslug' => $this->contenttype['singular_slug'],
                        'id'              => $this->id,
                        'slug'            => $slug,
                    ]
                )
            )
        );

        // Strip the query string generated by supplementary parameters.
        // since our $params contained all possible arguments and the ->generate()
        // added all $params which it didn't need in the query-string we can
        // safely strip the query-string.
        // NB. this does mean we don't support routes with query strings
        return preg_replace('/^([^?]*).*$/', '\\1', $link);
    }

    /**
     * Checks if the current record is set as the homepage.
     *
     * @return boolean
     */
    public function isHome()
    {
        $homepage = $this->app['config']->get('theme/homepage') ?: $this->app['config']->get('general/homepage');
        $uriID = $this->contenttype['singular_slug'] . '/' . $this->get('id');
        $uriSlug = $this->contenttype['singular_slug'] . '/' . $this->get('slug');

        return $uriID === $homepage || $uriSlug === $homepage;
    }

    /**
     * Retrieves the first route applicable to the content as a two-element array consisting of the binding and the
     * route array. Returns `null` if there is no applicable route.
     *
     * @return array|null
     */
    protected function getRoute()
    {
        $allroutes = $this->app['config']->get('routing');

        // First, try to find a custom route that's applicable
        foreach ($allroutes as $binding => $route) {
            if ($this->isApplicableRoute($route)) {
                return [$binding, $route];
            }
        }

        // Just return the 'generic' contentlink route.
        if (!empty($allroutes['contentlink'])) {
            return ['contentlink', $allroutes['contentlink']];
        }

        return null;
    }

    /**
     * Build a ContentType's route parameters
     *
     * @param array $route
     *
     * @return array
     */
    protected function getRouteRequirementParams(array $route)
    {
        $params = [];
        if (isset($route['requirements'])) {
            foreach ($route['requirements'] as $fieldName => $requirement) {
                if ('\d{4}-\d{2}-\d{2}' === $requirement) {
                    // Special case, if we need to have a date
                    $params[$fieldName] = substr($this->get($fieldName), 0, 10);
                } elseif ($this->getTaxonomy() !== null && !$this->getTaxonomy()->getField($fieldName)->isEmpty()) {
                    // This is for new storage handling of taxonomies in
                    // contentroutes. If in legacy it will fall back to the one
                    // below.
                    $params[$fieldName] = $this->getTaxonomy()->getField($fieldName)->first()->getSlug();
                } elseif (isset($this->taxonomy[$fieldName])) {
                    // Turn something like '/groups/meta' to 'meta'. This is
                    // only for legacy storage.
                    $tempKeys = array_keys($this->taxonomy[$fieldName]);
                    $tempValues = explode('/', array_shift($tempKeys));
                    $params[$fieldName] = array_pop($tempValues);
                } elseif ($this->get($fieldName)) {
                    $params[$fieldName] = $this->get($fieldName);
                } else {
                    // unknown
                    $params[$fieldName] = null;
                }
            }
        }

        return $params;
    }

    /**
     * Check if a route is applicable to this record.
     *
     * @param array $route
     *
     * @return boolean
     */
    protected function isApplicableRoute(array $route)
    {
        return (isset($route['contenttype']) && $route['contenttype'] === $this->contenttype['singular_slug'])
            || (isset($route['contenttype']) && $route['contenttype'] === $this->contenttype['slug'])
            || (isset($route['recordslug']) && $route['recordslug'] === $this->getReference());
    }

    /**
     * Get the reference to this record, to uniquely identify this specific record.
     *
     * @return string
     */
    protected function getReference()
    {
        $reference = $this->contenttype['singular_slug'] . '/' . $this->getLinkSlug();

        return $reference;
    }

    /**
     * Get a record's slug depending on the type of object used.
     *
     * @return string|int
     */
    private function getLinkSlug()
    {
        if ($this instanceof Content) {
            return $this->slug ?: $this->id;
        }

        if (isset($this->values['slug'])) {
            return $this->values['slug'];
        }

        return $this->id;
    }
}
