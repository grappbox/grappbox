using Grappbox.Helpers;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Media;

namespace Grappbox.Model
{
    class BugtrackerModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("creator")]
        public UserModel Creator { get; set; }

        [JsonProperty("projectId")]
        public int ProjectId { get; set; }

        [JsonProperty("title")]
        public string Title { get; set; }

        [JsonProperty("description")]
        public string Description { get; set; }

        [JsonProperty("parentId")]
        public object parent { get { return ParentId; } set { if (value == null) ParentId = 0; else ParentId = Convert.ToInt32(value); } }
        public int ParentId { get; set; }

        [JsonProperty("comment")]
        public string Comment { get; set; }

        [JsonProperty("createdAt")]
        public string CreatedAt { get; set; }

        [JsonProperty("editedAt")]
        public string EditedAt { get; set; }

        [JsonProperty("state")]
        public string State { get; set; }

        [JsonProperty("deletedAt")]
        public string DeletedAt { get; set; }

        [JsonProperty("tags")]
        public List<TagModel> Tags { get; set; }

        [JsonProperty("users")]
        public List<UserModel> Users { get; set; }

        public string Infos
        {
            get
            {
                return string.Format("By {0} {1} at {2}", Creator.Firstname, Creator.Lastname, DateTime.Parse(CreatedAt).ToLocalTime().ToString());
            }
        }
        public Visibility Visible
        {
            get { if (Creator.Id != AppGlobalHelper.CurrentUser.Id) return Visibility.Visible; return Visibility.Collapsed; }
        }
        public bool IdCheck
        {
            get { if (Creator.Id != AppGlobalHelper.CurrentUser.Id) return false; return true; }
        }
    }
}
