using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Data.Xml.Dom;
using Windows.Networking.PushNotifications;
using Windows.UI.Notifications;
using Windows.Web.Http;

namespace Grappbox.Helpers
{
    public static class NotificationManager
    {
        public static PushNotificationChannel NotificationChannel
        {
            get;
            set;
        }
        public static async Task RequestChannel()
        {
            try
            {
                NotificationManager.NotificationChannel = await PushNotificationChannelManager.CreatePushNotificationChannelForApplicationAsync();
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
            }
        }


        public static async Task SendToken()
        {
            Dictionary<string, object> data = new Dictionary<string, object>();

            data.Add("device_type", "WP");
            data.Add("device_token", NotificationChannel.Uri.ToString());
            data.Add("device_name", "WindowsPhone");
            HttpResponseMessage res = await HttpRequestManager.Post(data, "notification/device");
            if (res?.IsSuccessStatusCode == false)
            {
                Debug.WriteLine("Device register error");
                if (res.StatusCode <= HttpStatusCode.InternalServerError)
                {
                    Debug.WriteLine(res.Content.ReadAsStringAsync().GetResults());
                }
            }
        }
    }
}