using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Model
{
    public class Event
    {
        #region PublicFields

        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("projectId")]
        public int? ProjectId { get; set; }

        [JsonProperty("creator")]
        public Creator Creator { get; set; }

        [JsonProperty("title")]
        public string Title { get; set; }

        [JsonProperty("description")]
        public string Description { get; set; }

        [JsonProperty("beginDate")]
        public string BeginDate { get; set; }

        [JsonProperty("endDate")]
        public string EndDate { get; set; }

        [JsonProperty("createdAt")]
        public string CreatedAt { get; set; }

        [JsonProperty("editedAt")]
        public string EditedAt { get; set; }

        [JsonProperty("users")]
        public List<UserModel> Users { get; set; }

        public bool IsSelected { get; set; } = false;

        #endregion PublicFields

        #region BindingMethods

        public static string ExtractTime(string datetime)
        {
            DateTime time;
            try
            {
                time = Convert.ToDateTime(datetime);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return datetime;
            }
            return time.ToString("hh:mm");
        }

        public static string ExtractDate(string datetime)
        {
            DateTime date;
            try
            {
                date = Convert.ToDateTime(datetime);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return datetime;
            }
            return date.ToString("dd/MM/yyyy");
        }

        #endregion BindingMethods
    }
}