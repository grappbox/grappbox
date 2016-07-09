using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model.Whiteboard
{
    class ObjectModel
    {
        [JsonProperty("type")]
        public string Type { get; set; }

        [JsonProperty("color")]
        public string Color { get; set; }

        [JsonProperty("background")]
        public string Background { get; set; }

        [JsonProperty("lineweight")]
        public int LineWeight { get; set; }

        [JsonProperty("positionStart")]
        public Position PositionStart { get; set; }

        [JsonProperty("positionEnd")]
        public Position PositionEnd { get; set; }

        [JsonProperty("points")]
        public List<Position> Points { get; set; }

        [JsonProperty("radius")]
        public Position Radius { get; set; }

        [JsonProperty("text")]
        public string Text { get; set; }

        [JsonProperty("size")]
        public int Size { get; set; }

        [JsonProperty("isItalic")]
        public bool IsItalic { get; set; }

        [JsonProperty("isBold")]
        public bool IsBold { get; set; }
    }
}
