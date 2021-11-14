# Provinces

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)

<a name="basic"></a>
## Basic Usage

Returns a list of provinces, including the status of each province.

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

### Attributes

Additional context for the attributes returned.

| Attribute | Description |
| :- | :- |
| Code | Code or initial of the province. This is used as the primary identifier when aggregating data |
| Geographic | A boolean used to indicate whether the grouping is geographical or not. In the dataset, this allows for groupings like "Repatriated" |
| Data Status | This is set by the Manage utility to note the current status of reporting. |
| Updated At | Timestamp when reports for the province (or health region in the province) was updated |

<a name="parameters"></a>
## Parameters

| Parameter | Type | Description |
| :- | :- | :- |
| geo_only | Boolean | If true, only provinces marked as geographic will be returned. This is useful for excluding data from non-geographic entities such as Repatriated Canadians or the Federal Allocation for vaccinations. |

<a name="status"></a>
## Data Status
Additional context for the Data Status attribute.

| Status | Description |
| :- | :- |
| Waiting for report | This status indicated that an update is expected to happen in the current day, but has not yet occured.  |
| In progress | This status indicates that an update is in-progress and will be completed soon. Note that when this status is indicated, some or all data may not be updated yet.  |
| Reported | When this status is indicated, the province has been updated with final data for the day, and the update is complete. |
| No report expected today | When this status is indicated, the province is not expected to provide an update on the current day, and one should not be expected. |
| Custom | Custom statuses are used to communicate certain issues with a province's update including delays or partial updates. |
