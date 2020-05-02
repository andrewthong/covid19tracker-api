# Fatalities

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [Parameters](#parameters)
- [Single Record](#single)

<a name="basic"></a>
## Basic Usage

Returns the latest 100 fatalities.

| Method | URI |
| :- | :- |
| GET | `/fatalities` |

<a name="sample-response"></a>
## Sample Response

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 380,
      "province": "ON",
      "date": "2020-04-07"
    },
    ...
  ],
  "first_page_url": ".../fatalities?page=1",
  "from": 1,
  "last_page": 4,
  "last_page_url": ".../fatalities?page=4",
  "next_page_url": ".../fatalities?page=2",
  "path": ".../fatalities",
  "per_page": "100",
  "prev_page_url": null,
  "to": 100,
  "total": 380
}
```

<a name="parameters"></a>

## Parameters

| Parameter | Type | Description |
| :- | :- | :- |
| province | String | Filter fatalities to a specific province e.g. `QC` |
| per_page | Integer | Number of fatalities per page (max 1000) |
| order | `DESC`\|`ASC` | If `ASC`, oldest fatalities are shown first. Defaults to `DESC` |

### Example
`/fatalities?province=ON&per_page=50`

<a name="single"></a>

## Single Record

Returns a single fatality record by ID.

| Method | URI |
| :- | :- |
| GET | `/fatality/:id` |

### Example

`/fatality/123`