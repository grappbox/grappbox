using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;
using GrappBox.Model.Global;

namespace GrappBox.Model
{
    public class Event
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("projectId")]
        public int? ProjectId { get; set; }
        [JsonProperty("creator")]
        public Creator Creator { get; set; }
        [JsonProperty("type")]
        public EventType Type { get; set; }
        [JsonProperty("title")]
        public string Title { get; set; }
        [JsonProperty("description")]
        public string Description { get; set; }
        [JsonProperty("beginDate")]
        public DateModel BeginDate { get; set; }
        [JsonProperty("endDate")]
        public DateModel EndDate { get; set; }
        [JsonProperty("createdAt")]
        public DateModel CreatedAt { get; set; }
        [JsonProperty("editedAt")]
        public DateModel EditedAt { get; set; }
        [JsonProperty("deletedAt")]
        public DateModel DeletedAt { get; set; }
        public bool IsinRange(DateTime dt)
        {
            bool test;
            DateTime begin;
            test = DateTimeFormator.DateModelToDateTime(BeginDate, out begin);
            if (test == false)
                return false;
            DateTime end;
            test = DateTimeFormator.DateModelToDateTime(EndDate, out end);
            if (test == false)
                return false;
            if (DateTime.Compare(dt, begin) >= 0)
            {
                if (DateTime.Compare(dt, end) <= 0)
                    return true;
            }
            return false;
        }
    }
}
