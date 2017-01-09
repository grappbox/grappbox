using Grappbox.Model.Global;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Xaml.Media.Imaging;

namespace Grappbox.Model
{
    class CloudModel
    {
        [JsonProperty("type")]
        public string Type { get; set; }

        [JsonProperty("filename")]
        public string Filename { get; set; }

        [JsonProperty("is_secured")]
        public bool IsSecured { get; set; }

        [JsonProperty("size")]
        public double Size { get; set; }

        [JsonProperty("mimetype")]
        public string Mimetype { get; set; }

        [JsonProperty("last_modified")]
        public DateModel Timestamp { get; set; }

        [JsonProperty("stream_id")]
        public int StreamId { get; set; }

        public string Icon
        {
            get
            {
                if (Type == "file")
                    return "\uE24D";
                return "\uE2C7";
            }
        }

        public bool IsFile
        {
            get
            {
                if (Type == "file")
                    return true;
                return false;
            }
        }

        public string FileSize
        {
            get
            {
                string[] sizes = { "B", "KB", "MB", "GB" };
                double len = Size;
                int order = 0;
                while (len >= 1024 && ++order < sizes.Length)
                {
                    len = len / 1024;
                }

                // Adjust the format string to your preferences. For example "{0:0.#}{1}" would
                // show a single decimal place, and no space.
                return String.Format("{0:0.##} {1}", len, sizes[order]);
            }
        }

        public string Infos
        {
            get
            {
                return DateTime.Parse(Timestamp.Date).ToLocalTime().ToString();
            }
        }
    }
}
