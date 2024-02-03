<?php
class JFormComponentDropDown extends JFormComponent
{
    public $dropDownOptionArray = [];

    public $disabled = false;
    public $multiple = false;
    public $size = null;
    public $width = null;

    /**
     * Constructor
     */
    public function __construct($id, $label, $dropDownOptionArray, $optionArray = [])
    {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'jFormComponentDropDown';
        $this->label = $label;
        $this->dropDownOptionArray = $dropDownOptionArray;

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    public function getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled)
    {
        $option = new JFormElement('option', ['value' => $optionValue]);
        $option->update($optionLabel);

        if ($optionSelected) {
            $option->setAttribute('selected', 'selected');
        }

        if ($optionDisabled) {
            $option->setAttribute('disabled', 'disabled');
        }

        return $option;
    }

    public static function getCountryArray($selectedCountry = null)
    {
        $countryArray = [['value' => '', 'label'  => 'Select a Country', 'disabled' => true], ['value' => 'US', 'label'  => 'United States of America'], ['value' => 'AF', 'label'  => 'Afghanistan'], ['value' => 'AL', 'label'  => 'Albania'], ['value' => 'DZ', 'label'  => 'Algeria'], ['value' => 'AS', 'label'  => 'American Samoa'], ['value' => 'AD', 'label'  => 'Andorra'], ['value' => 'AO', 'label'  => 'Angola'], ['value' => 'AI', 'label'  => 'Anguilla'], ['value' => 'AQ', 'label'  => 'Antarctica'], ['value' => 'AG', 'label'  => 'Antigua and Barbuda'], ['value' => 'AR', 'label'  => 'Argentina'], ['value' => 'AM', 'label'  => 'Armenia'], ['value' => 'AW', 'label'  => 'Aruba'], ['value' => 'AU', 'label'  => 'Australia'], ['value' => 'AT', 'label'  => 'Austria'], ['value' => 'AZ', 'label'  => 'Azerbaijan'], ['value' => 'BS', 'label'  => 'Bahamas'], ['value' => 'BH', 'label'  => 'Bahrain'], ['value' => 'BD', 'label'  => 'Bangladesh'], ['value' => 'BB', 'label'  => 'Barbados'], ['value' => 'BY', 'label'  => 'Belarus'], ['value' => 'BE', 'label'  => 'Belgium'], ['value' => 'BZ', 'label'  => 'Belize'], ['value' => 'BJ', 'label'  => 'Benin'], ['value' => 'BM', 'label'  => 'Bermuda'], ['value' => 'BT', 'label'  => 'Bhutan'], ['value' => 'BO', 'label'  => 'Bolivia'], ['value' => 'BA', 'label'  => 'Bosnia and Herzegovina'], ['value' => 'BW', 'label'  => 'Botswana'], ['value' => 'BV', 'label'  => 'Bouvet Island'], ['value' => 'BR', 'label'  => 'Brazil'], ['value' => 'IO', 'label'  => 'British Indian Ocean Territory'], ['value' => 'BN', 'label'  => 'Brunei'], ['value' => 'BG', 'label'  => 'Bulgaria'], ['value' => 'BF', 'label'  => 'Burkina Faso'], ['value' => 'BI', 'label'  => 'Burundi'], ['value' => 'KH', 'label'  => 'Cambodia'], ['value' => 'CM', 'label'  => 'Cameroon'], ['value' => 'CA', 'label'  => 'Canada'], ['value' => 'CV', 'label'  => 'Cape Verde'], ['value' => 'KY', 'label'  => 'Cayman Islands'], ['value' => 'CF', 'label'  => 'Central African Republic'], ['value' => 'TD', 'label'  => 'Chad'], ['value' => 'CL', 'label'  => 'Chile'], ['value' => 'CN', 'label'  => 'China'], ['value' => 'CX', 'label'  => 'Christmas Island'], ['value' => 'CC', 'label'  => 'Cocos (Keeling) Islands'], ['value' => 'CO', 'label'  => 'Columbia'], ['value' => 'KM', 'label'  => 'Comoros'], ['value' => 'CG', 'label'  => 'Congo'], ['value' => 'CK', 'label'  => 'Cook Islands'], ['value' => 'CR', 'label'  => 'Costa Rica'], ['value' => 'CI', 'label'  => 'Cote D\'Ivorie (Ivory Coast)'], ['value' => 'HR', 'label'  => 'Croatia (Hrvatska)'], ['value' => 'CU', 'label'  => 'Cuba'], ['value' => 'CY', 'label'  => 'Cyprus'], ['value' => 'CZ', 'label'  => 'Czech Republic'], ['value' => 'CD', 'label'  => 'Democratic Republic of Congo (Zaire)'], ['value' => 'DK', 'label'  => 'Denmark'], ['value' => 'DJ', 'label'  => 'Djibouti'], ['value' => 'DM', 'label'  => 'Dominica'], ['value' => 'DO', 'label'  => 'Dominican Republic'], ['value' => 'TP', 'label'  => 'East Timor'], ['value' => 'EC', 'label'  => 'Ecuador'], ['value' => 'EG', 'label'  => 'Egypt'], ['value' => 'SV', 'label'  => 'El Salvador'], ['value' => 'GQ', 'label'  => 'Equatorial Guinea'], ['value' => 'ER', 'label'  => 'Eritrea'], ['value' => 'EE', 'label'  => 'Estonia'], ['value' => 'ET', 'label'  => 'Ethiopia'], ['value' => 'FK', 'label'  => 'Falkland Islands (Malvinas)'], ['value' => 'FO', 'label'  => 'Faroe Islands'], ['value' => 'FJ', 'label'  => 'Fiji'], ['value' => 'FI', 'label'  => 'Finland'], ['value' => 'FR', 'label'  => 'France'], ['value' => 'FX', 'label'  => 'France), Metropolitanarray('], ['value' => 'GF', 'label'  => 'French Guinea'], ['value' => 'PF', 'label'  => 'French Polynesia'], ['value' => 'TF', 'label'  => 'French Southern Territories'], ['value' => 'GA', 'label'  => 'Gabon'], ['value' => 'GM', 'label'  => 'Gambia'], ['value' => 'GE', 'label'  => 'Georgia'], ['value' => 'DE', 'label'  => 'Germany'], ['value' => 'GH', 'label'  => 'Ghana'], ['value' => 'GI', 'label'  => 'Gibraltar'], ['value' => 'GR', 'label'  => 'Greece'], ['value' => 'GL', 'label'  => 'Greenland'], ['value' => 'GD', 'label'  => 'Grenada'], ['value' => 'GP', 'label'  => 'Guadeloupe'], ['value' => 'GU', 'label'  => 'Guam'], ['value' => 'GT', 'label'  => 'Guatemala'], ['value' => 'GN', 'label'  => 'Guinea'], ['value' => 'GW', 'label'  => 'Guinea-Bissau'], ['value' => 'GY', 'label'  => 'Guyana'], ['value' => 'HT', 'label'  => 'Haiti'], ['value' => 'HM', 'label'  => 'Heard and McDonald Islands'], ['value' => 'HN', 'label'  => 'Honduras'], ['value' => 'HK', 'label'  => 'Hong Kong'], ['value' => 'HU', 'label'  => 'Hungary'], ['value' => 'IS', 'label'  => 'Iceland'], ['value' => 'IN', 'label'  => 'India'], ['value' => 'ID', 'label'  => 'Indonesia'], ['value' => 'IR', 'label'  => 'Iran'], ['value' => 'IQ', 'label'  => 'Iraq'], ['value' => 'IE', 'label'  => 'Ireland'], ['value' => 'IL', 'label'  => 'Israel'], ['value' => 'IT', 'label'  => 'Italy'], ['value' => 'JM', 'label'  => 'Jamaica'], ['value' => 'JP', 'label'  => 'Japan'], ['value' => 'JO', 'label'  => 'Jordan'], ['value' => 'KZ', 'label'  => 'Kazakhstan'], ['value' => 'KE', 'label'  => 'Kenya'], ['value' => 'KI', 'label'  => 'Kiribati'], ['value' => 'KW', 'label'  => 'Kuwait'], ['value' => 'KG', 'label'  => 'Kyrgyzstan'], ['value' => 'LA', 'label'  => 'Laos'], ['value' => 'LV', 'label'  => 'Latvia'], ['value' => 'LB', 'label'  => 'Lebanon'], ['value' => 'LS', 'label'  => 'Lesotho'], ['value' => 'LR', 'label'  => 'Liberia'], ['value' => 'LY', 'label'  => 'Libya'], ['value' => 'LI', 'label'  => 'Liechtenstein'], ['value' => 'LT', 'label'  => 'Lithuania'], ['value' => 'LU', 'label'  => 'Luxembourg'], ['value' => 'ME', 'label' => 'Montenegro'], ['value' => 'MO', 'label'  => 'Macau'], ['value' => 'MK', 'label'  => 'Macedonia'], ['value' => 'MG', 'label'  => 'Madagascar'], ['value' => 'MW', 'label'  => 'Malawi'], ['value' => 'MY', 'label'  => 'Malaysia'], ['value' => 'MV', 'label'  => 'Maldives'], ['value' => 'ML', 'label'  => 'Mali'], ['value' => 'MT', 'label'  => 'Malta'], ['value' => 'MH', 'label'  => 'Marshall Islands'], ['value' => 'MQ', 'label'  => 'Martinique'], ['value' => 'MR', 'label'  => 'Mauritania'], ['value' => 'MU', 'label'  => 'Mauritius'], ['value' => 'YT', 'label'  => 'Mayotte'], ['value' => 'MX', 'label'  => 'Mexico'], ['value' => 'FM', 'label'  => 'Micronesia'], ['value' => 'MD', 'label'  => 'Moldova'], ['value' => 'MC', 'label'  => 'Monaco'], ['value' => 'MN', 'label'  => 'Mongolia'], ['value' => 'MS', 'label'  => 'Montserrat'], ['value' => 'MA', 'label'  => 'Morocco'], ['value' => 'MZ', 'label'  => 'Mozambique'], ['value' => 'MM', 'label'  => 'Myanmar (Burma)'], ['value' => 'NA', 'label'  => 'Namibia'], ['value' => 'NR', 'label'  => 'Nauru'], ['value' => 'NP', 'label'  => 'Nepal'], ['value' => 'NL', 'label'  => 'Netherlands'], ['value' => 'AN', 'label'  => 'Netherlands Antilles'], ['value' => 'NC', 'label'  => 'New Caledonia'], ['value' => 'NZ', 'label'  => 'New Zealand'], ['value' => 'NI', 'label'  => 'Nicaragua'], ['value' => 'NE', 'label'  => 'Niger'], ['value' => 'NG', 'label'  => 'Nigeria'], ['value' => 'NU', 'label'  => 'Niue'], ['value' => 'NF', 'label'  => 'Norfolk Island'], ['value' => 'KP', 'label'  => 'North Korea'], ['value' => 'MP', 'label'  => 'Northern Mariana Islands'], ['value' => 'NO', 'label'  => 'Norway'], ['value' => 'OM', 'label'  => 'Oman'], ['value' => 'PK', 'label'  => 'Pakistan'], ['value' => 'PW', 'label'  => 'Palau'], ['value' => 'PA', 'label'  => 'Panama'], ['value' => 'PG', 'label'  => 'Papua New Guinea'], ['value' => 'PY', 'label'  => 'Paraguay'], ['value' => 'PE', 'label'  => 'Peru'], ['value' => 'PH', 'label'  => 'Philippines'], ['value' => 'PN', 'label'  => 'Pitcairn'], ['value' => 'PL', 'label'  => 'Poland'], ['value' => 'PT', 'label'  => 'Portugal'], ['value' => 'PR', 'label'  => 'Puerto Rico'], ['value' => 'QA', 'label'  => 'Qatar'], ['value' => 'RE', 'label'  => 'Reunion'], ['value' => 'RO', 'label'  => 'Romania'], ['value' => 'RS', 'label' => 'Serbia'], ['value' => 'RU', 'label'  => 'Russia'], ['value' => 'RW', 'label'  => 'Rwanda'], ['value' => 'SH', 'label'  => 'Saint Helena'], ['value' => 'KN', 'label'  => 'Saint Kitts and Nevis'], ['value' => 'LC', 'label'  => 'Saint Lucia'], ['value' => 'PM', 'label'  => 'Saint Pierre and Miquelon'], ['value' => 'VC', 'label'  => 'Saint Vincent and The Grenadines'], ['value' => 'SM', 'label'  => 'San Marino'], ['value' => 'ST', 'label'  => 'Sao Tome and Principe'], ['value' => 'SA', 'label'  => 'Saudi Arabia'], ['value' => 'SN', 'label'  => 'Senegal'], ['value' => 'SC', 'label'  => 'Seychelles'], ['value' => 'SL', 'label'  => 'Sierra Leone'], ['value' => 'SG', 'label'  => 'Singapore'], ['value' => 'SK', 'label'  => 'Slovak Republic'], ['value' => 'SI', 'label'  => 'Slovenia'], ['value' => 'SB', 'label'  => 'Solomon Islands'], ['value' => 'SO', 'label'  => 'Somalia'], ['value' => 'ZA', 'label'  => 'South Africa'], ['value' => 'GS', 'label'  => 'South Georgia'], ['value' => 'KR', 'label'  => 'South Korea'], ['value' => 'ES', 'label'  => 'Spain'], ['value' => 'LK', 'label'  => 'Sri Lanka'], ['value' => 'SD', 'label'  => 'Sudan'], ['value' => 'SR', 'label'  => 'Suriname'], ['value' => 'SJ', 'label'  => 'Svalbard and Jan Mayen'], ['value' => 'SZ', 'label'  => 'Swaziland'], ['value' => 'SE', 'label'  => 'Sweden'], ['value' => 'CH', 'label'  => 'Switzerland'], ['value' => 'SY', 'label'  => 'Syria'], ['value' => 'TW', 'label'  => 'Taiwan'], ['value' => 'TJ', 'label'  => 'Tajikistan'], ['value' => 'TZ', 'label'  => 'Tanzania'], ['value' => 'TH', 'label'  => 'Thailand'], ['value' => 'TG', 'label'  => 'Togo'], ['value' => 'TK', 'label'  => 'Tokelau'], ['value' => 'TO', 'label'  => 'Tonga'], ['value' => 'TT', 'label'  => 'Trinidad and Tobago'], ['value' => 'TN', 'label'  => 'Tunisia'], ['value' => 'TR', 'label'  => 'Turkey'], ['value' => 'TM', 'label'  => 'Turkmenistan'], ['value' => 'TC', 'label'  => 'Turks and Caicos Islands'], ['value' => 'TV', 'label'  => 'Tuvalu'], ['value' => 'UG', 'label'  => 'Uganda'], ['value' => 'UA', 'label'  => 'Ukraine'], ['value' => 'AE', 'label'  => 'United Arab Emirates'], ['value' => 'UK', 'label'  => 'United Kingdom'], ['value' => 'US', 'label'  => 'United States of America'], ['value' => 'UM', 'label'  => 'United States Minor Outlying Islands'], ['value' => 'UY', 'label'  => 'Uruguay'], ['value' => 'UZ', 'label'  => 'Uzbekistan'], ['value' => 'VU', 'label'  => 'Vanuatu'], ['value' => 'VA', 'label'  => 'Vatican City (Holy See)'], ['value' => 'VE', 'label'  => 'Venezuela'], ['value' => 'VN', 'label'  => 'Vietnam'], ['value' => 'VG', 'label'  => 'Virgin Islands (British)'], ['value' => 'VI', 'label'  => 'Virgin Islands (US)'], ['value' => 'WF', 'label'  => 'Wallis and Futuna Islands'], ['value' => 'EH', 'label'  => 'Western Sahara'], ['value' => 'WS', 'label'  => 'Western Samoa'], ['value' => 'YE', 'label'  => 'Yemen'], ['value' => 'YU', 'label'  => 'Yugoslavia'], ['value' => 'ZM', 'label'  => 'Zambia'], ['value' => 'ZW', 'label'  => 'Zimbabwe']];

        if (!empty($selectedCountry)) {
            foreach ($countryArray as &$countryOption) {
                if ($countryOption['value'] == $selectedCountry) {
                    $countryOption['selected'] = true;
                    break;
                }
            }
        } else {
            $countryArray[0]['selected'] = true;
        }

        return $countryArray;
    }

    public static function getStateArray($selectedState = null)
    {
        $stateArray = [['value' => '', 'label'  => 'Select a State', 'disabled' => true], ['value' => 'AL', 'label'  => 'Alabama'], ['value' => 'AK', 'label'  => 'Alaska'], ['value' => 'AZ', 'label'  => 'Arizona'], ['value' => 'AR', 'label'  => 'Arkansas'], ['value' => 'CA', 'label'  => 'California'], ['value' => 'CO', 'label'  => 'Colorado'], ['value' => 'CT', 'label'  => 'Connecticut'], ['value' => 'DE', 'label'  => 'Delaware'], ['value' => 'DC', 'label'  => 'District of Columbia'], ['value' => 'FL', 'label'  => 'Florida'], ['value' => 'GA', 'label'  => 'Georgia'], ['value' => 'HI', 'label'  => 'Hawaii'], ['value' => 'ID', 'label'  => 'Idaho'], ['value' => 'IL', 'label'  => 'Illinois'], ['value' => 'IN', 'label'  => 'Indiana'], ['value' => 'IA', 'label'  => 'Iowa'], ['value' => 'KS', 'label'  => 'Kansas'], ['value' => 'KY', 'label'  => 'Kentucky'], ['value' => 'LA', 'label'  => 'Louisiana'], ['value' => 'ME', 'label'  => 'Maine'], ['value' => 'MD', 'label'  => 'Maryland'], ['value' => 'MA', 'label'  => 'Massachusetts'], ['value' => 'MI', 'label'  => 'Michigan'], ['value' => 'MN', 'label'  => 'Minnesota'], ['value' => 'MS', 'label'  => 'Mississippi'], ['value' => 'MO', 'label'  => 'Missouri'], ['value' => 'MT', 'label'  => 'Montana'], ['value' => 'NE', 'label'  => 'Nebraska'], ['value' => 'NV', 'label'  => 'Nevada'], ['value' => 'NH', 'label'  => 'New Hampshire'], ['value' => 'NJ', 'label'  => 'New Jersey'], ['value' => 'NM', 'label'  => 'New Mexico'], ['value' => 'NY', 'label'  => 'New York'], ['value' => 'NC', 'label'  => 'North Carolina'], ['value' => 'ND', 'label'  => 'North Dakota'], ['value' => 'OH', 'label'  => 'Ohio'], ['value' => 'OK', 'label'  => 'Oklahoma'], ['value' => 'OR', 'label'  => 'Oregon'], ['value' => 'PA', 'label'  => 'Pennsylvania'], ['value' => 'RI', 'label'  => 'Rhode Island'], ['value' => 'SC', 'label'  => 'South Carolina'], ['value' => 'SD', 'label'  => 'South Dakota'], ['value' => 'TN', 'label'  => 'Tennessee'], ['value' => 'TX', 'label'  => 'Texas'], ['value' => 'UT', 'label'  => 'Utah'], ['value' => 'VT', 'label'  => 'Vermont'], ['value' => 'VA', 'label'  => 'Virginia'], ['value' => 'WA', 'label'  => 'Washington'], ['value' => 'WV', 'label'  => 'West Virginia'], ['value' => 'WI', 'label'  => 'Wisconsin'], ['value' => 'WY', 'label'  => 'Wyoming']];

        if (!empty($selectedState)) {
            foreach ($stateArray as &$stateOption) {
                if ($stateOption['value'] == $selectedState) {
                    $stateOption['selected'] = true;
                    break;
                }
            }
        } else {
            $stateArray[0]['selected'] = true;
        }

        return $stateArray;
    }

    public static function getMonthArray()
    {
        return [['value' => '01', 'label'  => 'January'], ['value' => '02', 'label'  => 'February'], ['value' => '03', 'label'  => 'March'], ['value' => '04', 'label'  => 'April'], ['value' => '05', 'label'  => 'May'], ['value' => '06', 'label'  => 'June'], ['value' => '07', 'label'  => 'July'], ['value' => '08', 'label'  => 'August'], ['value' => '09', 'label'  => 'September'], ['value' => '10', 'label'  => 'October'], ['value' => '11', 'label'  => 'November'], ['value' => '12', 'label'  => 'December']];
    }

    public static function getYearArray($minYear, $maxYear)
    {
        $yearArray = [];
        for ($i = $maxYear - $minYear; $i > 0; $i--) {
            $yearArray[] = ['value' => $i + $minYear, 'label' => $i + $minYear];
        }
        return $yearArray;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        // Div tag contains everything about the component
        $div = parent::generateComponentDiv();

        // Select tag
        $select = new JFormElement('select', [
            'id' => $this->id,
            'name' => $this->name,
            'class' => $this->class . ' form-control',
        ]);

        // Only use if disabled is set, otherwise will throw an error
        if ($this->disabled) {
            $select->setAttribute('disabled', 'disabled');
        }
        if ($this->multiple) {
            $select->setAttribute('multiple', 'multiple');
        }
        if ($this->size != null) {
            $select->setAttribute('size', $this->size);
        }
        if ($this->width != null) {
            $select->setAttribute('style', 'width:'.$this->width);
        }

        // Check for any opt groups
        $optGroupArray = [];
        foreach ($this->dropDownOptionArray as $dropDownOption) {
            if (isset($dropDownOption['optGroup']) && !empty($dropDownOption['optGroup'])) {
                $optGroupArray[] = $dropDownOption['optGroup'];
            }
        }
        $optGroupArray = array_unique($optGroupArray);

        // Create the optgroup elements
        foreach ($optGroupArray as $optGroup) {
            ${$optGroup} = new JFormElement('optgroup', ['label' => $optGroup]);
        }

        // Add any options to their appropriate optgroup
        foreach ($this->dropDownOptionArray as $dropDownOption) {
            if (isset($dropDownOption['optGroup']) && !empty($dropDownOption['optGroup'])) {
                $optionValue = $dropDownOption['value'] ?? '';
                $optionLabel =  $dropDownOption['label'] ?? '';
                $optionSelected =  $dropDownOption['selected'] ?? false;
                $optionDisabled =  $dropDownOption['disabled'] ?? false;
                $optionOptGroup =  $dropDownOption['optGroup'] ?? '';

                ${$dropDownOption['optGroup']}->insert($this->getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled));
            }
        }

        // Add any options that are not in an opt group to the select
        foreach ($this->dropDownOptionArray as $dropDownOption) {
            // Handle optgroup addition - only add the group if you haven't seen it yet
            if (isset($dropDownOption['optGroup']) && !empty($dropDownOption['optGroup']) && !isset(${$dropDownOption['optGroup'].'Added'})) {
                $select->insert(${$dropDownOption['optGroup']});
                ${$dropDownOption['optGroup'].'Added'} = true;
            }
            // Add any other elements
            elseif (!isset($dropDownOption['optGroup'])) {
                $optionValue = $dropDownOption['value'] ?? '';
                $optionLabel =  $dropDownOption['label'] ?? '';
                $optionSelected =  $dropDownOption['selected'] ?? false;
                $optionDisabled =  $dropDownOption['disabled'] ?? false;
                $optionOptGroup =  $dropDownOption['optGroup'] ?? '';

                $select->insert($this->getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled));
            }
        }

        // Add the select box to the div
        $div->insert('<div class="col-sm-8">'.$select.'</div>');

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div, $this->id.'-div');

        return $div->__toString();
    }
}
