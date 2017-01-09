using Grappbox.Model.Global;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class ProjectAdvancementModel
    {
        [JsonProperty("date")]
        public string Date { get; set; }

        [JsonProperty("percentage")]
        public float Percentage { get; set; }

        [JsonProperty("progress")]
        public int Progress { get; set; }

        [JsonProperty("totalTasks")]
        public int TotalTasks { get; set; }

        [JsonProperty("finishedTasks")]
        public int FinishedTasks { get; set; }

        public string DateFormat
        {
            get { return DateTime.Parse(Date).ToLocalTime().ToString("yyyy-MM-dd"); }
        }
    }
}
