# Subregions

---

- [Overview](#overview)
- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [Specific Region](#single)
- [Population Values](#pop)
- [Accessing The Data](#data)

<a name="overview"></a>
## Overview
Subregions were introduced in late 2021 to take advantage of the availability of sub-health region level vaccination data in some provinces and territories.

Subregion data only includes data on vaccinations, and may return data as a raw number or as a percentage, depending on the source data. Regardless, the type of source data is indicated in each response.

The current number of subregions by province can be found below:

| Province | Number of Subregions |
| :- | :- |
| AB | 132 |
| NL | 38 |
| NT | 30 |
| SK | 13 |
| ON | 514 |
| MB | 79 |

<a name="overview"></a>
## Basic Usage
Returns a list of regions, including the subregion code, which becomes helpful when seeking data for specific subregions with other endpoints.

| Method | URI |
| :- | :- |
| GET | `/sub-regions` |

<a name="sample-response"></a>
## Sample Response

```json
{
  "data": [
    {
      "code":"AB001",
      "province":"AB",
      "zone":"SOUTH",
      "region":"CROWSNEST PASS",
      "population":6280
    },
  ...
  ]
}
```

<a name="single"></a>
## Get a Specific Subregion

Returns a single health subregion record by subregion code

| Method | URI |
| :- | :- |
| GET | `/sub-regions/:CODE` |

### Example

`/sub-regions/AB001`

<a name="pop"></a>
## Population Values

Population values are returned with each subregion, and are used in calculations to interconvert vaccination totals and percentages. However, it is important to note that the population values for some regions only include the ***eligible*** population and exclude the portion of the population which is not yet able to receive a COVID-19 vaccine.

This is reflected in both the population value and percentages returned. Please familiarize yourself with the table below, and check back frequently as this is subject to change.

| Province | Subregion Percentage Type|
| :- | :- |
|AB| Total Population| 
|SK| Total Population| 
|NT| Total Population|
|ON| Total Population| 
|MB| **Eligible Population Only**| 
|NL| **Eligible Population Only**| 

<a name="data"></a>
## Accessing the Data

See the [Vaccination Data](/{{route}}/{{version}}/vaccinations) page for more information on accessing subregion-level vaccination data.
