using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model.Whiteboard
{
    class PullModel
    {
        [JsonProperty("add")]
        public List<WhiteboardObject> addObjects { get; set; }

        [JsonProperty("delete")]
        public List<WhiteboardObject> delObjects { get; set; }
    }
}
