using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI;

namespace Grappbox.Helpers
{
    static class ColorExtension
    {
        public static Color FromHexaString(this Color color, string hexa)
        {
            hexa = hexa.Replace("#", string.Empty);
            if (hexa.Length == 3)
            {
                StringBuilder str = new StringBuilder();
                for (int i = 0; i < hexa.Length; i++)
                {
                    str.Append(string.Format("{0}{1}", hexa[i], hexa[i]));
                }
                hexa = str.ToString();
            }
            byte a = (byte)(Convert.ToUInt32("255", 16));
            byte r = (byte)(Convert.ToUInt32(hexa.Substring(0, 2), 16));
            byte g = (byte)(Convert.ToUInt32(hexa.Substring(2, 2), 16));
            byte b = (byte)(Convert.ToUInt32(hexa.Substring(4, 2), 16));
            return Color.FromArgb(a, r, g, b);
        }
        public static string ColorToHexa(this Color color)
        {
            string hex = "#";
            hex += color.R.ToString("X2");
            hex += color.G.ToString("X2");
            hex += color.B.ToString("X2");
            return hex;
        }
    }
}
