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
        [JsonProperty("title")]
        public string Title { get; set; }
        [JsonProperty("description")]
        public string Description { get; set; }
        [JsonProperty("begin_date")]
        public string BeginDate { get; set; }
        [JsonProperty("end_date")]
        public string EndDate { get; set; }
    }
}
