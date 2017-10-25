<?php
$tests = [
  [
    'page' => 'https://www.kepa.fi/',
  ],
  [
    'auth' => false,
    'page' => 'https://www.kepa.fi/jaseninfo-palvelut/jasenrekisteri',
    'response_code' => 200,
    'search_type' => 'xpath',
    'search' => '//*[@id="select-org-type"]/option[2]',
    'expected_type' => 'code',
    'expected' => 'if (strlen($got) > 5) return TRUE;',
  ],
  [
    'auth' => false,
    'page' => 'https://www.kepa.fi/jaseninfo-palvelut/koulutustarjonta/ilmoittautumislomake',
    'response_code' => 200,
    'search_type' => 'xpath',
    'search' => '//*[@id="edit-training"]/option[2]',
    'expected_type' => 'code',
    'expected' => 'if (strlen($got) > 5) return TRUE;',
  ],
  [
    /* a listing is working and gives some results */
    'auth' => false,
    'page' => 'https://www.kepa.fi/uutiset-media/uutiset',
    'response_code' => 200,
    'search_type' => 'xpath',
    'search' => '//*[@id="block-kepafi-theme-content"]/section/div/div/div/div/div/div/section/div/div[1]/article/div[2]/h1',
    'expected_type' => 'code',
    'expected' => 'if (strlen($got) > 5) return TRUE;',
  ],
  [
    /* tapahtumakalenteri and its facets work */
    'auth' => false,
    'page' => 'https://www.kepa.fi/tapahtumat/tapahtumakalenteri',
    'response_code' => 200,
    'search_type' => 'xpath',
    'search' => '//*[@class="facet-item__count"]',
    'expected_type' => 'code',
    'expected' => 'preg_match("/^\(([0-9]+)\)$/", $got, $matches); $count = (int)$matches[1]; if ($count > 0 && $count < 1000) return TRUE;',
  ],
];
