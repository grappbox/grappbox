using Grappbox.Model.Global;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class LateTaskModel
    {
        [JsonProperty("user")]
        public UsersModel User { get; set; }

        [JsonProperty("role")]
        public string Role { get; set; }

        [JsonProperty("date")]
        public string Date { get; set; }

        [JsonProperty("lateTasks")]
        public int LateTasks { get; set; }

        [JsonProperty("ontimeTasks")]
        public int OntimeTasks { get; set; }
    }
}
