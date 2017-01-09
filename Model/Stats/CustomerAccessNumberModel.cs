using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class CustomerAccessNumberModel
    {
        [JsonProperty("actual")]
        public int Actual { get; set; }

        [JsonProperty("maximum")]
        public int Maximum { get; set; }
    }
}
