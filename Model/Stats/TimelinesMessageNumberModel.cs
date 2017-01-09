using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class TimelinesMessageNumberModel
    {
        [JsonProperty("team")]
        public int Team { get; set; }
        [JsonProperty("customer")]
        public int Customer { get; set; }

    }
}
