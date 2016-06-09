using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class CloudModel
    {
        [JsonProperty("type")]
        public string Type { get; set; }

        [JsonProperty("filename")]
        public string Filename { get; set; }

        [JsonProperty("is_secured")]
        public bool IsSecured { get; set; }

        [JsonProperty("mimetype")]
        public string Mimetype { get; set; }

        [JsonProperty("timestamp ")]
        public int timestamp { get; set; }

        [JsonProperty("stream_id")]
        public int streamId { get; set; }
    }
}
