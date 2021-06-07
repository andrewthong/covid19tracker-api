# Vaccine Age Groups

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [Parameters](#parameters)
- [Split](#split)
- [By Province](#by-province)

<a name="basic"></a>

## Basic Usage

By default, this request returns a week-by-week report of vaccine stats by various age groups.

| Method | URI |
| :- | :- |
| GET | `/vaccines/age-groups` |

<a name="sample-response"></a>

## Sample Response

```json
{
  "province": "All",
  "data": [
    {
      "date": "2021-05-01",
      "data": "{\"80+\": {\"full\": 292848..."
    },
    ...
  ]
}
```

### data
An array of vaccination reports.
- **date** — in `Y-m-d` format
- **data** — JSON string containing stats split into age group.

*due to reporting standard shifts overtime, the JSON string data may not be consistent across weeks. Minimal effort is taken to normalize some of this data.

<a name="parameters"></a>

## Parameters

| Parameter | Type | Description |
| :- | :- | :- |
| after | Date | Returns reports on or after the specified date. |
| before | Date | Returns reports on or before the specified date. |
| group | String | Returns reports for a specific group. Must be URL encoded |

### Example

`/vaccines/age-groups?after=2021-03-01&group=80%2B`

<a name="split"></a>
## Split

This will return all provinces (except ALL/Canada).

| Method | URI |
| :- | :- |
| GET | `/vaccines/age-groups/split` |

### Data
An array of vaccination reports but with an addition when split.
- **province** — province code

<a name="by-province"></a>

## By Province

Filter the data to the province level by providing a province code e.g. `SK`. All parameters are supported.

| Method | URI |
| :- | :- |
| GET | `/vaccines/age-groups/province/:code` |

### Example

`/vaccines/age-groups/province/ab`