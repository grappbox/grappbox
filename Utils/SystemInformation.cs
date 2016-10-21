using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Security.Cryptography;
using Windows.Security.Cryptography.Core;
using Windows.Storage.Streams;
using Windows.System.Profile;

namespace GrappBox.Utils
{
    public class SystemInformation
    {
        public static string GetUniqueIdentifier()
        {
            HardwareToken hardwareToken = HardwareIdentification.GetPackageSpecificToken(null);
            IBuffer hardwareId = hardwareToken.Id;
            HashAlgorithmProvider hasher = HashAlgorithmProvider.OpenAlgorithm("MD5");
            IBuffer hashed = hasher.HashData(hardwareId);
            string hashedString = CryptographicBuffer.EncodeToHexString(hashed);
            return hashedString;
        }

        public static object GetStaticResource(string resourceName)
        {
            object resource = null;
            resource = App.Current.Resources[resourceName];
            return resource;
        }
    }

    //internal class SettingsManager
    //{
    //    public static bool OptionExist(string optName)
    //    {
    //        return Windows.Storage.ApplicationData.Current.LocalSettings.Values.ContainsKey(optName);
    //    }

    //    public static T getOption<T>(string optName)
    //    {
    //        Object value = Windows.Storage.ApplicationData.Current.LocalSettings.Values[optName];
    //        if (value == null)
    //        {
    //            return default(T);
    //        }
    //        else
    //            return (T)value;
    //    }

    //    public static int getOption(string optName)
    //    {
    //        Object value = Windows.Storage.ApplicationData.Current.LocalSettings.Values[optName];
    //        if (value == null)
    //        {
    //            return -1;
    //        }
    //        else
    //            return (int)value;
    //    }

    //    public static void setOption(string optName, Object value)
    //    {
    //        Windows.Storage.ApplicationData.Current.LocalSettings.Values[optName] = value;
    //    }
    //}
}