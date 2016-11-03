﻿using GrappBox.Helpers;
using Newtonsoft.Json;
using System;
using Windows.UI.Xaml;

namespace GrappBox.Model
{
    public class TimelineModel
    {
        [JsonProperty("id")]
        public int Id { get; set; }

        [JsonProperty("creator")]
        public UserModel Creator { get; set; }

        [JsonProperty("timelineId")]
        public int TimelineId { get; set; }

        [JsonProperty("title")]
        public string Title { get; set; }

        [JsonProperty("message")]
        public string Message { get; set; }

        [JsonProperty("comment")]
        public string Comment { get; set; }

        [JsonProperty("parentId")]
        public object parent { get { return ParentId; } set { if (value == null) ParentId = 0; else ParentId = Convert.ToInt32(value); } }
        public int ParentId { get; set; }

        [JsonProperty("createdAt")]
        public string CreatedAt { get; set; }

        [JsonProperty("editedAt")]
        public string EditedAt { get; set; }

        [JsonProperty("nbComment")]
        public int NbComment { get; set; }

        public bool IdCheck
        {
            get { if (Creator.Id != AppGlobalHelper.CurrentUser.Id) return false; return true; }
        }
        public bool ReadOnly
        {
            get { if (Creator.Id != AppGlobalHelper.CurrentUser.Id) return true; return false; }
        }

        public string TextDate
        {
            get
            {
                if (EditedAt != null)
                    return "By " + Creator.Firstname + " " + Creator.Lastname + " at " + DateTime.Parse(EditedAt).ToLocalTime().ToString();
                else
                    return "By " + Creator.Firstname + " " + Creator.Lastname + " at " + DateTime.Parse(CreatedAt).ToLocalTime().ToString();
            }
        }
    }
}
