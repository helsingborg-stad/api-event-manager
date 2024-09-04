(function() {
  acf.add_filter('date_time_picker_args', function( args, field ){
    args.showSecond = false;
    return args;
  });
})();