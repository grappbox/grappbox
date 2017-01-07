using Newtonsoft.Json;
using System;
using System.Globalization;
using System.Text;
using Windows.UI.Xaml.Media;

namespace Grappbox.Model
{
    public class TagModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }
        [JsonProperty("name")]
        public string Name { get; set; }
        [JsonProperty("color")]
        public string Color { get; set; }
        
        public SolidColorBrush ColorTag
        {
            get
            {
                return GetSolidColorBrush(Color);
            }
        }

        public SolidColorBrush GetSolidColorBrush(string hex)
        {
            hex = hex.Replace("#", string.Empty);
            if (hex.Length == 3)
            {
                StringBuilder str = new StringBuilder();
                for (int i = 0; i < hex.Length; i++)
                {
                    str.Append(string.Format("{0}{1}", hex[i], hex[i]));
                }
                hex = str.ToString();
            }
            byte a = (byte)(Convert.ToUInt32("255", 16));
            byte r = (byte)(Convert.ToUInt32(hex.Substring(0, 2), 16));
            byte g = (byte)(Convert.ToUInt32(hex.Substring(2, 2), 16));
            byte b = (byte)(Convert.ToUInt32(hex.Substring(4, 2), 16));
            SolidColorBrush myBrush = new SolidColorBrush(Windows.UI.Color.FromArgb(a, r, g, b));
            return myBrush;
        }
    }
}
