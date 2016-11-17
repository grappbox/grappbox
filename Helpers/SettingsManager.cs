using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Helpers
{
    internal class SettingsManager
    {
        public static bool OptionExist(string optName)
        {
            return Windows.Storage.ApplicationData.Current.LocalSettings.Values.ContainsKey(optName);
        }

        public static T getOption<T>(string optName)
        {
            Object value = Windows.Storage.ApplicationData.Current.LocalSettings.Values[optName];
            if (value == null)
            {
                return default(T);
            }
            else
                return (T)value;
        }

        public static int getOption(string optName)
        {
            Object value = Windows.Storage.ApplicationData.Current.LocalSettings.Values[optName];
            if (value == null)
            {
                return -1;
            }
            else
                return (int)value;
        }

        public static void setOption(string optName, Object value)
        {
            Windows.Storage.ApplicationData.Current.LocalSettings.Values[optName] = value;
        }
    }
}