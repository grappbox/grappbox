﻿using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage.Streams;
using Windows.UI.Xaml.Media.Imaging;

namespace GrappBox.Model.Global
{
    class AvatarModel
    {
        [JsonProperty("avatar")]
        public string Avatar { get; set; }
    }
}
