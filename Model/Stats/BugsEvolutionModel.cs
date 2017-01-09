using Grappbox.Model.Global;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class BugsEvolutionModel
    {
        [JsonProperty("date")]
        public string Date { get; set; }

        [JsonProperty("createdBugs")]
        public int CreatedBugs { get; set; }

        [JsonProperty("closedBugs")]
        public int ClosedBugs { get; set; }

        public string DateFormat
        {
            get { return DateTime.Parse(Date).ToLocalTime().ToString("yyyy-MM-dd"); }
        }
    }
}
