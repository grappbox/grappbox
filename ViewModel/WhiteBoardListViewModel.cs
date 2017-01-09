// ***********************************************************************
// Assembly         : Grappbox
// Author           : pfeytout
// Created          : 07-02-2016
//
// Last Modified By : pfeytout
// Last Modified On : 12-17-2016
// ***********************************************************************
// <copyright file="WhiteBoardListViewModel.cs" company="Grappbox"
//     Copyright ©  2016
// </copyright>
// <summary></summary>
// ***********************************************************************
using Grappbox.Model;
using Grappbox.ViewModel;
using Grappbox.Helpers;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml.Controls;

namespace Grappbox.ViewModel
{
    /// <summary>
    /// Class WhiteBoardListViewModel.
    /// </summary>
    /// <seealso cref="Grappbox.ViewModel.ViewModelBase" />
    class WhiteBoardListViewModel : ViewModelBase
    {
        /// <summary>
        /// The whiteboards
        /// </summary>
        private ObservableCollection<WhiteBoardListModel> _whiteboards;
        /// <summary>
        /// Gets or sets the whiteboards.
        /// </summary>
        /// <value>The whiteboards.</value>
        public ObservableCollection<WhiteBoardListModel> Whiteboards
        {
            get { return _whiteboards; }
            set { _whiteboards = value;  NotifyPropertyChanged("Whiteboards"); }
        }
        /// <summary>
        /// Initializes a new instance of the <see cref="WhiteBoardListViewModel"/> class.
        /// </summary>
        public WhiteBoardListViewModel()
        {
        }
        public async System.Threading.Tasks.Task GetWhiteboards()
        {
            SessionHelper session = SessionHelper.GetSession();
            HttpResponseMessage res = await HttpRequest.HttpRequestManager.Get(Constants.ListWhiteboards, session.ProjectId);
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                Whiteboards =  SerializationHelper.DeserializeArrayJson<ObservableCollection<WhiteBoardListModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("Whiteboards");
            }
            else
            {
                Debug.WriteLine("Can't get whiteboard list");
            }
        }
        /// <summary>
        /// Creates the whiteboard.
        /// </summary>
        /// <param name="name">The name.</param>
        /// <returns>System.Threading.Tasks.Task.</returns>
        public async Task CreateWhiteboard(string name)
        {
            SessionHelper session = SessionHelper.GetSession();
            int id = session?.ProjectId ?? default(int);
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("projectId", id);
            props.Add("whiteboardName", name);
            HttpResponseMessage res = await HttpRequest.HttpRequestManager.Post(props, Constants.CreateWhiteboard);
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                WhiteBoardListModel wlm = SerializationHelper.DeserializeJson<WhiteBoardListModel>(await res.Content.ReadAsStringAsync());
                Whiteboards.Add(wlm);
            }
            else
            {
                Debug.WriteLine("Can't create Whiteboard");
            }
        }
    }
}
