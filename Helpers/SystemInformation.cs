using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Security.Cryptography;
using Windows.Security.Cryptography.Core;
using Windows.Storage.Streams;
using Windows.System.Profile;

namespace Grappbox.Helpers
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

        public static T GetStaticResource<T>(string resourceName) where T : class
        {
            T resource = null;

            resource = App.Current.Resources[resourceName] as T;
            return resource;
        }
    }
}