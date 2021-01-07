# Provinces

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)

<a name="basic"></a>
## Basic Usage

Returns a list of provinces.

| Method | URI |
| :- | :- |
| GET | `/provinces` |

<a name="sample-response"></a>
## Sample Response

```json
[
  {
    "id": 1,
    "code": "ON",
    "name": "Ontario",
    "data_source": null,
    "population": 14711827,
    "area": 917741,
    "gdp": 857384,
    "geographic": 1,
    "data_status": "Reported",
    "created_at": null,
    "updated_at": "2021-01-06T22:38:43.000000Z",
    "density": 16.030478097851137
  },
  ...
]
```

<a name="parameters"></a>
## Parameters

| Parameter | Type | Description |
| :- | :- | :- |
| geo_only | Boolean | If true, only provinces marked as geographic will be returned |