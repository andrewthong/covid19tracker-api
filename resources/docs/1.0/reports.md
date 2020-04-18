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
| GET | `/report` |

<a name="sample-response"></a>

## Sample Response

```json
{
  "province": "All",
  "data": [
    {
      "date": "2020-01-30",
      "new_tests": 3000,
      "new_cases": 30,
      "total_hospitalizations": 15,
      "total_criticals": 3,
      "new_fatalities": 0,
      "total_recoveries": 100
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
- **new_{stat}** — daily new additions 
- **total_{stat}** — a total count for the day

<a name="parameters"></a>

## Parameters

| Parameter | Type | Description |
| :- | :- | :- |
| cumulative | Boolean | When `true`, the response will include a rolling total count of all new_{stat}. <br><br>_cumulative is unavailable when any date parameter is used._ |
| fill_dates | Boolean | When `true`, the response will fill any missing dates in-between with blank entries. Can be useful for some visualizations. |
| stat | String | Reports only on the set statistic e.g. `recoveries` |
| date | Date | Reports only on a single date e.g. `2020-01-01` |
| after | Date | Returns reports on or after the specified date. |
| before | Date | Returns reports on or before the specified date. If `after` is provided, this defaults to today. |

### Example

`report?stat=tests&cumulative=true&fill_dates=true`



<a name="by-province"></a>

## By Province

Filter the data to the province level by providing a province code e.g. `SK`. All parameters are supported.

| Method | URI |
| :- | :- |
| GET | `/report/province/:code` |

### Example

`report/province/ab?cumulative=true`
