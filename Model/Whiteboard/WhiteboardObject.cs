using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model.Whiteboard
{
    class WhiteboardObject
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("whiteboardId")]
        public int WhiteboardId { get; set; }
        [JsonProperty("createdAt")]
        public DateModel CreatedAt { get; set; }
        [JsonProperty("deledtedAt")]
        public DateModel DeledtedAt { get; set; }
        [JsonProperty("object")]
        public List<ObjectModel> Object { get; set; }
    }
}
