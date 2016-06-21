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

        [JsonProperty("object")]
        public ObjectModel Object { get; set; }
    }
}
