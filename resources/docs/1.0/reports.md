# Reports

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [Parameters](#parameters)
- [By Province](#by-province)

<a name="basic"></a>

## Basic Usage

By default, this returns a day-to-day rolling summary of stats for all provinces.

| Method | URI |
| :- | :- |
| GET | `/reports` |

<a name="sample-response"></a>

## Sample Response

```json
{
  "province": "All",
  "data": [
    {
      "date": "2020-03-24",
      "change_cases": 661,
      "change_fatalities": 3,
      "change_tests": 7743,
      "change_hospitalizations": 49,
      "change_criticals": 19,
      "change_recoveries": 74,
      "total_cases": 2806,
      "total_fatalities": 27,
      "total_tests": 110989,
      "total_hospitalizations": 146,
      "total_criticals": 70,
      "total_recoveries": 186
    },
    ...
  ]
}
```

### province
When _All_, report is for all provinces.

### data
An array of daily reports. Note the prefix for all reporting stats.
- **date** — in `Y-m-d` format
- **change_{stat}** — daily change compared to the previous report
- **total_{stat}** — the total, cumulative count up to the date

<a name="parameters"></a>

## Parameters

| Parameter | Type | Description |
| :- | :- | :- |
| fill_dates | Boolean | When `true`, the response will fill any missing dates in-between with blank entries. Can be useful for some visualizations. |
| stat | String | Reports only on the set statistic e.g. `recoveries` |
| date | Date | Reports only on a single date e.g. `2020-01-01` |
| after | Date | Returns reports on or after the specified date. |
| before | Date | Returns reports on or before the specified date. If `after` is provided, this defaults to today. |

### Example

`reports?stat=tests&fill_dates=true`



<a name="by-province"></a>

## By Province

Filter the data to the province level by providing a province code e.g. `SK`. All parameters are supported.

| Method | URI |
| :- | :- |
| GET | `/reports/province/:code` |

### Example

`reports/province/ab`
