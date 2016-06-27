using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;

namespace GrappBox.Model
{
    public class MeetingDashBoard
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("type")]
        public string Type { get; set; }
        [JsonProperty("title")]
        public string Title { get; set; }
        [JsonProperty("description")]
        public string Description { get; set; }
        [JsonProperty("begin_date")]
        public DateModel BeginDate { get; set; }
        [JsonProperty("end_date")]
        public DateModel EndDate { get; set; }
    }
}
