
"""

run.py_plots

wikimediafoundation.org
Ryan Faulkner
December 16th, 2010


Pulls data from db9.faulkner and generates plots.


"""

import fundraiser_reporting as fa

tar = fa.TotalAmountsReporting()
blpr = fa.BannerLPReporting()


# Run the total amount plots
tar.run_hr()
tar.run_day()

# Run the banner/lp plots
blpr.run('LP', 'don_per_view')
blpr.run('BAN', 'don_per_imp')
blpr.run('BAN', 'click_rate')
blpr.run('LP', 'conversion_rate')

# Run the banner/lp plots
blpr.run('LP-TEST', 'don_per_view')
blpr.run('BAN-TEST', 'don_per_imp')