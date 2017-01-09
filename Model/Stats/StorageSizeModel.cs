using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model.Stats
{
    class StorageSizeModel
    {
        [JsonProperty("occupied")]
        public int Occupied { get; set; }

        [JsonProperty("total")]
        public int Total { get; set; }
    }
}
