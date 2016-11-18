using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model.Global;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage;
using Windows.Storage.Streams;
using Windows.UI;
using Windows.UI.Xaml.Media;
using Windows.Web.Http;

namespace Grappbox.Model
{
    public class Occupations
    {
        public class OccupationUser
        {
            private string _id;

            [JsonProperty("id")]
            public string Id { get; set; }

            [JsonProperty("firstName")]
            public string FirstName { get; set; }

            [JsonProperty("lastName")]
            public string LastName { get; set; }
        }

        public string UserName
        {
            get { return User.FirstName + " " + User.LastName; }
        }

        [JsonProperty("user")]
        public OccupationUser User { get; set; }

        [JsonProperty("occupation")]
        public string Occupation { get; set; }

        [JsonProperty("number_of_tasks_begun")]
        public int Tasks_begun { get; set; }

        [JsonProperty("number_of_ongoing_tasks")]
        public int Tasks_Ongoing { get; set; }

        public SolidColorBrush OccupationColor
        {
            get
            {
                if (Occupation == "busy")
                    return SystemInformation.GetStaticResource("RedGrappboxBrush") as SolidColorBrush;
                return SystemInformation.GetStaticResource("GreenGrappboxBrush") as SolidColorBrush;
            }
        }
    }
}