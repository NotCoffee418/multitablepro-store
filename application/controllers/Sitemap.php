<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sitemap extends CI_Controller {
	public function index() {
		// Get the cached version
		$xml = $this->Apcu->get('sitemap');
		if ($xml == null) {
			// Get the data we need
			$this->load->model(array('Products', 'Versions'));
			$allProductGroups = $this->Products->list_product_groups();

			// open xml sitemap
			$xml = '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			// Add landing page
			$xml .= $this->sitemapEntry('', 'monthly', 0.9);

			// Add download pages
			$xml .= $this->sitemapEntry('/download', 'monthly', 0.8);
			foreach ($allProductGroups as $pg) {
				// Get latest version date
				$verInfo = $this->Versions->get_version_info($pg->short_name, 'RELEASE', 'latest');
				if (isset($verInfo["requested_version"]->release_date))
					$updateDate = substr($verInfo["requested_version"]->release_date, 0, 10);
				else $updateDate = null;
				$xml .= $this->sitemapEntry('/download/'.$pg->short_name, 'monthly', 0.7, $updateDate);
			}

			// Add store pages
			$xml .= $this->sitemapEntry('/store', 'monthly', 0.8);
			foreach ($allProductGroups as $pg) {
				$xml .= $this->sitemapEntry('/store/'.$pg->short_name, 'monthly');
			}

			// Add support
			$xml .= $this->sitemapEntry('/support', 'monthly');

			// Add register & login
			$xml .= $this->sitemapEntry('/user/register', 'monthly');
			$xml .= $this->sitemapEntry('/user/login', 'monthly');

			// Add eula
			$xml .= $this->sitemapEntry('/eula', 'monthly', 0.2);

			// add privacy policy
			$xml .= $this->sitemapEntry('/privacy-policy', 'monthly', 0.2);

			// End urlset
			$xml .= '</urlset>';

			// Cache it
			$this->Apcu->set('sitemap', $xml);
		}

		// Display result
		$this->output
			->set_content_type('application/xml')
			->set_output($xml);
	}

	private function sitemapEntry($path, $changeFreq = 'monthly', $priority = null, $lastMod = null) {
		$entry = '<url>';
		$entry .= "<loc>". site_url($path) ."</loc>";
		$entry .= "<changefreq>$changeFreq</changefreq>";
		$entry .= $priority == null ? '' : "<priority>$priority</priority>";
		$entry .= $lastMod == null ? '' : "<lastmod>$lastMod</lastmod>";
		$entry .= '</url>';
		return $entry;
	}
}
