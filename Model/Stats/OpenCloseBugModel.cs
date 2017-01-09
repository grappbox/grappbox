using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class OpenCloseBugModel
    {
        [JsonProperty("open")]
        public int Open { get; set; }

        [JsonProperty("closed")]
        public int Closed { get; set; }
    }
}
