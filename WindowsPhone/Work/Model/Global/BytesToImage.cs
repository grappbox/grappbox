using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Globalization.DateTimeFormatting;
using Windows.Storage;
using Windows.Storage.Streams;
using Windows.UI.Xaml.Media.Imaging;
using System.Runtime.InteropServices.WindowsRuntime;

namespace GrappBox.Model.Global
{
    public static class BytesToImage
    {
        public static StorageFolder localFolder = ApplicationData.Current.LocalFolder;

        public static BitmapImage String64ToImage(string base64)
        {
            byte[] imageBytes = Convert.FromBase64String(base64);
            using (InMemoryRandomAccessStream ms = new InMemoryRandomAccessStream())
            {
                using (DataWriter writer = new DataWriter(ms.GetOutputStreamAt(0)))
                {
                    writer.WriteBytes((byte[])imageBytes);
                    writer.StoreAsync().GetResults();
                }

                BitmapImage image = new BitmapImage();
                image.SetSource(ms);
                return image;
            }
        }
        public static BitmapImage GetDefaultAvatar()
        {
            BitmapImage bmi = new BitmapImage();
            Uri uri = new Uri("ms-appx:///Assets/user.png");
            bmi.UriSource = uri;
            return bmi;
        }
        public static BitmapImage GetDefaultLogo()
        {
            BitmapImage bmi = new BitmapImage();
            Uri uri = new Uri("ms-appx:///Assets/grappbox-logo.png");
            bmi.UriSource = uri;
            return bmi;
        }
        public static async System.Threading.Tasks.Task<bool> StoreImage(string img, string fileName)
        {
            try
            {
                StorageFile imageFile = await localFolder.CreateFileAsync(fileName + ".txt", CreationCollisionOption.ReplaceExisting);
                await FileIO.WriteBytesAsync(imageFile, Convert.FromBase64String(img));
            }
            catch (Exception)
            {
                return false;
            }
            return true;
        }
        public static async System.Threading.Tasks.Task<string> GetStoredImage(string fileName)
        {
            string imageString = null;
            try
            {
                StorageFile imageFile = await localFolder.GetFileAsync(fileName + ".txt");
                IBuffer buff = await FileIO.ReadBufferAsync(imageFile);
                byte[] bytes = buff.ToArray();
                imageString = Convert.ToBase64String(bytes);
            }
            catch (Exception)
            {
                return null;
            }
            return imageString;
        }
    }
}
