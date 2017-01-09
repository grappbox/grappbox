using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Global
{
    class DateModel
    {
        [JsonProperty("date")]
        public string Date { get; set; }
    }
}
