using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Ressources
{
    class SettingsManager
    {
        public static T getOption<T>(string optName)
        {
           Object value = Windows.Storage.ApplicationData.Current.LocalSettings.Values[optName];
            if (value == null)
                return default(T);
            else
                return (T)value;
        }
        public static void setOption(string optName, Object value)
        {
            Windows.Storage.ApplicationData.Current.LocalSettings.Values[optName] = value;
        }
    }
}
