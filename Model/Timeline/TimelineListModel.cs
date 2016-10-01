using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class TimelineListModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("typeId")]
        public int TypeId { get; set; }

        [JsonProperty("projectId")]
        public int ProjectId { get; set; }

        [JsonProperty("name")]
        public string Name { get; set; }

        [JsonProperty("typeName")]
        public string TypeName { get; set; }
    }
}
