using Grappbox.Model.Global;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class ProjectTimeLimitsModel
    {
        [JsonProperty("projectStart")]
        public string ProjectStart { get; set; }

        [JsonProperty("projectEnd")]
        public string ProjectEnd { get; set; }
    }
}
